<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $description = $request->input('description');
        $tags = $request->input('tags');
        $categories = $request->input('categories');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        $product = Product::with(['category','galleries']);

        if($name)
            $product->where('name', 'like', '%' . $name . '%');

        if($description)
            $product->where('description', 'like', '%' . $description . '%');

        if($categories)
            $product->where('categories_id', $categories);

        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data list produk berhasil diambil'
        );
    }

    public function read(Request $request){
        $start = $request->start;
        $length = $request->length;
        $search = strtoupper($request->search['value']);
        $sort = $request->columns[$request->order[0]['column']]['data'];
        $dir = $request->order[0]['dir'];

        $query = Payment::select('payments.*');
        if($search != ""){
            $query->whereRaw("upper(name) like '%$search%'");
        }
        $recordsTotal = $query->count();
        $query->offset($start);
        $query->limit($length);
        $query->orderBy($sort, $dir);
        
        $payments = $query->get();

        $data = [];
        foreach($payments as $row){
            // $arrTitle = ["","Frontend Web Programmer","Fullstack Web Programmer","Quality Control"];
			// $row->title_name = $arrTitle[$row->title_id];
            // $row->date_apply = date('d/m/Y',strtotime($row->created_at));
            $row->no = ++$start;
			$data[] = $row;
		}
        return response()->json([
            'draw'=>$request->draw,
			'recordsTotal'=>$recordsTotal,
			'recordsFiltered'=>$recordsTotal,
			'data'=>$data
        ], 200);
    }

    public function status(Request $request)
    {
        $reff = $request->reff;

        $payment = Payment::select('*','created_at as paid')->first();
        if($reff)
            $payment->where('reff', $reff);

        if($payment){
            //Data created, return success response
            return response()->json([
                    'amount' => $payment->amount,
                    'reff'   => $payment->reff,
                    'name'   => $payment->name,
                    'expired'=> $payment->expired,
                    'paid'   => $payment->paid,
                    'code'   => $payment->code,
                    'status' => $payment->status,
                ], 200);
        }else{
            return new PaymentResource(false, 'Payment get failed!');
        }
    }

    public function payment(Request $request)
    {
        // Validate data
        $data = $request->only('reff');
        $validator = Validator::make($data, [
            'reff' => 'required',
        ]);
        // Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $check = Payment::where('reff', $request->reff)->first();
        if(!$check){
            return response()->json([
                'success' => false,
                'code'    => 401,
                'message' => 'Payment does not exist!.'
            ], 401);
        }

        $payment = Payment::where('reff', $request->reff)->first()->update(['status' => 'paid']);
        if($payment){
            $data = Payment::where('reff', $request->reff)->first();
            if($data){
                return response()->json([
                    'amount' => $data->amount,
                    'reff'   => $data->reff,
                    'name'   => $data->name,
                    'code'   => $data->code,
                    'status' => $data->status,
                ], 200);
            }
        }else{
            return new PaymentResource(false, 'Payment failed!');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate data
        // $data = $request->only('body', 'post_id');
        // $validator = Validator::make($data, [
        //     'body' => 'required',
        //     'post_id' => 'required'
        // ]);
        //Send failed response if request is not valid
        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->messages()], 200);
        // }

        //Request is valid, create new data
        $payment = Payment::create([
            'amount'    => $request->amount + 2500,
            'reff'      => $request->reff,
            'name'      => $request->name,
            'code'      => "8834".$request->code,
            'status'    => "waiting",
            'expired'   => date('Y-m-d H:i:s'),
        ]);
        if($payment){
            //Data created, return success response
            return new PaymentResource(true, 'Payment created successfully!', $payment);
        }else{
            return new PaymentResource(false, 'Payment created failed!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        //
    }
}
