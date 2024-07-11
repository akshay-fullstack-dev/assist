<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Transaction;
use App\User;
use Validator;
use DB;

class TransactionController extends Controller {

    protected $auth;

    public function __construct() {
        $this->auth = auth()->guard('user');
        $this->pageLimit = config('settings.pageLimitFront');
    }

    public function index() {
        //remove search array
        session()->forget('SEARCH');
        //end code

        $user_id = $this->auth->user()->id;
        $transactions = Transaction::where('user_id', $user_id)->orderBy('created_at', 'DESC')->paginate($this->pageLimit);
        return view('frontend.transactionList', compact('transactions'));
    }

    /**
     * search transaction from database.
     * 
     * @author Dhaval
     * @param  Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request) {
        $user_id = $this->auth->user()->id;
        //reset search
        if ($request->has('reset')) {
            $request->session()->forget('SEARCH');
            return redirect('transaction');
        }
        //end code

        if ($request->get('search_by') != '') {
            session(['SEARCH.SEARCH_BY' => trim($request->get('search_by'))]);
        }

        if ($request->get('search_txt') != '') {
            session(['SEARCH.SEARCH_TXT' => trim($request->get('search_txt'))]);
        }

        if ($request->get('search_date') != '') {
            session(['SEARCH.SEARCH_DATE' => trim($request->get('search_date'))]);
        }

        if ($request->session()->get('SEARCH.SEARCH_BY') != '') {
            $query = Transaction::select('*')->where('user_id',$user_id);

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'trans_id') {
                $query->where('trans_id', $request->session()->get('SEARCH.SEARCH_TXT'));
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'transaction_date') {
                $date = date('Y-m-d', strtotime($request->session()->get('SEARCH.SEARCH_DATE')));
                $query->where(DB::raw("date(created_at)"), '=', $date);
            }

            $transactions = $query->orderBy('created_at', 'desc')->paginate($this->pageLimit);

//            $transactions = $query->orderBy('created_at', 'DESC')->toSql();
//            echo $transactions;
//            $bindings = $query->getBindings();
//            dd($bindings);

            return view('frontend.transactionList', compact('transactions'));
        } else {
            return redirect('transaction');
        }
    }

    /**
     *  export transactions-list.csv
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=transactions-list.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Transaction ID', 'Payment Method', 'Credit', 'Amount', 'Transaction Date','Transaction Status'));

        $user_id = $this->auth->user()->id;
        $transactions = Transaction::where('user_id', $user_id)->orderBy('created_at', 'DESC')->get();
        foreach ($transactions as $data) {
            $amt = $data->amount.' '.$data->currency;
            $date = date('d-m-Y h:i:s A', strtotime($data->created_at));
            fputcsv($output, array(
                $data->trans_id,
                $data->payment_method,
                $data->credit,
                $amt,
                $date,
                $data->status
                    )
            );
        }
        fclose($output);
        exit;
    }

}
