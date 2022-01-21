<?php

namespace App\Http\Controllers;

use App\Models\ShoppingOrder;
use Illuminate\Http\Request;
use App\Repositories\ShoppingOrderRepository;
use App\Helpers\formattedApiResponse;

class ShoppingOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ShoppingOrderRepository $ShoppingOrderRepo)
    {
        $ShoppingOrders = $ShoppingOrderRepo->getShoppingOrders();
        return formattedApiResponse::getJson($ShoppingOrders);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShoppingOrder  $ShoppingOrder
     * @return \Illuminate\Http\Response
     */
    public function show(ShoppingOrder $ShoppingOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ShoppingOrder  $ShoppingOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(ShoppingOrder $ShoppingOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ShoppingOrder  $ShoppingOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShoppingOrder $ShoppingOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShoppingOrder  $ShoppingOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShoppingOrder $ShoppingOrder)
    {
        //
    }
}
