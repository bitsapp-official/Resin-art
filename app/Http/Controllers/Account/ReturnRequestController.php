<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReturnRequestController extends Controller
{
    public function index()
    {
        $returnRequests = Auth::user()->returnRequests()
            ->with(['order', 'orderItem'])
            ->latest()
            ->paginate(10);

        return view('account.returns.index', compact('returnRequests'));
    }

    public function create(Request $request)
    {
        $orders = Auth::user()->orders()->where('status', 'DELIVERED')->get();
        $selectedOrder = null;
        if ($request->filled('order_id')) {
            $selectedOrder = Auth::user()->orders()->find($request->order_id);
        }

        return view('account.returns.create', compact('orders', 'selectedOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'order_item_id' => ['nullable', 'exists:order_items,id'],
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        $order = Auth::user()->orders()->findOrFail($request->order_id);

        if (!$order->is_returnable) {
            return back()->with('error', 'Returns can only be requested for delivered orders.');
        }

        ReturnRequest::create([
            'user_id' => Auth::id(),
            'order_id' => $order->id,
            'order_item_id' => $request->order_item_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'REQUESTED',
        ]);

        return redirect()->route('account.returns.index')->with('success', 'Return request submitted successfully.');
    }
}
