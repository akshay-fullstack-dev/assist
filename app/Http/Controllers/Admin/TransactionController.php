<?php

namespace App\Http\Controllers\Admin;

use App\Currency;
use App\Http\Controllers\Controller;
use App\PaymentSetting;
use App\Transaction;
use App\User;
use DB;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    /**
     * Transaction Model
     * @var Transaction
     */
    protected $transaction;
    protected $pageLimit;
    protected $userList;
    /**
     * Inject the models.
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->pageLimit = config('settings.pageLimit');
        $all_transaction = Transaction::orderBy('created_at', 'DESC')->with('user')->get();
        if ($all_transaction->count() > 0) {
            foreach ($all_transaction as $trans) {
                if (isset($trans->user->id)) {
                    $this->userList[$trans->user->id] = $trans->user->firstname . " " . $trans->user->lastname;
                }
            }
        }
    }

    /**
     * Display a listing of transactions
     *
     * @return Response
     */
    public function index()
    {
        //remove search array
        session()->forget('SEARCH');
        //end code
        $userList = ['' => 'Select User'];
        $userId = request()->segment(3);
        if ($userId) {
            // Grab user the transactions
            $transactions = Transaction::userBy($userId)->orderBy('created_at', 'DESC')->with('user')->paginate($this->pageLimit);
        } else {
            // Grab all the transactions
            $transactions = Transaction::orderBy('created_at', 'DESC')->with('user')->paginate($this->pageLimit);
        }
        $admin_comission_percent = PaymentSetting::where('commission', '!=', '')->first();

        // get all user
        $all_transaction = Transaction::orderBy('created_at', 'DESC')->with('user')->get();
        if ($all_transaction->count() > 0) {
            foreach ($all_transaction as $trans) {
                if (isset($trans->user->id)) {
                    $userList[$trans->user->id] = $trans->user->firstname . " " . $trans->user->lastname;
                }
            }
        }
        $currency = Currency::select('code')->where('id', '=', $admin_comission_percent->currency_id)->first();
        $currency = $currency->code;
        // Show the transaction
        return view('admin/transactionList', compact('transactions', 'userList', 'currency'));
    }

    /**
     * Display the specified transaction.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $transaction = Transaction::findOrFail($id);
        return view('admin/transactionDetails', compact('transaction'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        Transaction::destroy($id);

        session()->flash('success_message', trans('admin/transaction.transaction_delete_message'));
        $array = array();
        $array['success'] = true;
        //$array['message'] = 'Transaction deleted successfully!';
        echo json_encode($array);
    }

    /**
     * search transaction from database.
     *
     * @author Dhaval
     * @param  Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        if ($request->has('reset')) {
            $request->session()->forget('SEARCH');
            return redirect('admin/transaction');
        }
        //end code

        if ($request->get('search_by') != '') {
            session(['SEARCH.SEARCH_BY' => trim($request->get('search_by'))]);
        }

        if ($request->get('search_txt') != '') {
            session(['SEARCH.SEARCH_TXT' => trim($request->get('search_txt'))]);
        }

        if ($request->get('user_id') != '') {
            session(['SEARCH.USER_ID' => trim($request->get('user_id'))]);
        }

        if ($request->get('search_date') != '') {
            session(['SEARCH.SEARCH_DATE' => trim($request->get('search_date'))]);
            session(['SEARCH.SEARCH_DATE_TO' => trim($request->get('search_date_to'))]);
        }

        if ($request->session()->get('SEARCH.SEARCH_BY') != '') {
            $query = Transaction::select('*');

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'user') {
                $query->where('user_id', $request->session()->get('SEARCH.USER_ID'));
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'trans_id') {
                $query->where('trans_id', $request->session()->get('SEARCH.SEARCH_TXT'));
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'transaction_date') {
                $date = date('Y-m-d', strtotime($request->session()->get('SEARCH.SEARCH_DATE')));
                $date_to = date('Y-m-d', strtotime($request->session()->get('SEARCH.SEARCH_DATE_TO')));
                $query->where([[DB::raw("date(created_at)"), '>=', $date], [DB::raw("date(created_at)"), '<=', $date_to]]);
            }

            if ($request->session()->get('SEARCH.SEARCH_BY') == 'online') {
                $query->where('payment_method', 2);
            }
            if ($request->session()->get('SEARCH.SEARCH_BY') == 'offline') {
                $query->where('payment_method', 1);
            }

            $transactions = $query->orderBy('created_at', 'desc')->paginate($this->pageLimit);
            $admin_comission_percent = PaymentSetting::where('commission', '!=', '')->first();

            $currency = Currency::select('code')->where('id', '=', $admin_comission_percent->currency_id)->first();
            $currency = $currency->code;
            $userList = $this->userList;
            return view('admin/transactionList', compact('transactions', 'userList', 'currency'));
        } else {
            return redirect('admin/transaction');
        }
    }

    /**
     *  export transactions-list.csv
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=transaction_" . strtotime(date('Y-m-d H:i:s')) . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        );

        $reviews = Transaction::orderBy('created_at', 'DESC')->get();
        $columns = array("Transaction", "Booking ID", "User Name", "Payment Method", "Total Amount", "Admin Commission", "Vender Amount", "Transaction Date", "Transaction Status");
        $callback = function () use ($reviews, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($reviews as $data) {
                $date = date('d-m-Y h:i:s A', strtotime($data->created_at));
                $payment_method = 'Cash on delivery';
                if ($data->payment_method == 0) {
                    $payment_method = 'Online';
                }
                $currency = Currency::select('code')->where('id', '=', $data->currency)->first();
                $currency = $currency->code;
                fputcsv($file, array(
                    $data->trans_id, $data->booking_id, $data->user->firstname, $payment_method, $data->amount, $data->admin_amount, $data->vender_amount, $date, $data->status,
                ));
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}
