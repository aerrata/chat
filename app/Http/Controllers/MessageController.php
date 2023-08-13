<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Message::query()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $message = auth()->user()->messages()->create([
            'content' => request()->content
        ]);

        broadcast(new NewMessage(auth()->user(), $message))->toOthers();

        return [
            'status' => 'Succeed!',
            'data' => [
                'message' => $message->load('user')
            ]
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
