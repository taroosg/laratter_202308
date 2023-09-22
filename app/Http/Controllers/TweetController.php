<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Tweet;
use App\Models\User;
use Auth;

class TweetController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $tweets = Tweet::getAllOrderByUpdated_at();
    return response()->view('tweet.index', compact('tweets'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return response()->view('tweet.create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'tweet' => 'required | max:191',
      'description' => 'required',
    ]);
    if ($validator->fails()) {
      return redirect()
        ->route('tweet.create')
        ->withInput()
        ->withErrors($validator);
    }
    $data = $request->merge(['user_id' => Auth::user()->id])->all();
    $result = Tweet::create($data);
    return redirect()->route('tweet.index');
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    // id指定して1件のデータをとる
    $tweet = Tweet::find($id);
    return response()->view('tweet.show', compact('tweet'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    $tweet = Tweet::find($id);
    return response()->view('tweet.edit', compact('tweet'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    $validator = Validator::make($request->all(), [
      'tweet' => 'required | max:191',
      'description' => 'required',
    ]);
    if ($validator->fails()) {
      return redirect()
        ->route('tweet.edit', $id)
        ->withInput()
        ->withErrors($validator);
    }

    $result = Tweet::find($id)->update($request->all());
    return redirect()->route('tweet.index');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    $tweet = Tweet::find($id)->delete();
    return redirect()->route('tweet.index');
  }

  public function mydata()
  {
    // Userモデルに定義したリレーションを使用してデータを取得する．
    $tweets = User::query()
      ->find(Auth::user()->id)
      ->userTweets()
      ->orderBy('created_at', 'desc')
      ->with('user')
      ->get();
    return response()->view('tweet.index', compact('tweets'));
  }

  public function timeline()
  {
    $followings = User::find(Auth::id())->followings->pluck('id')->all();

    $tweets = Tweet::query()
      ->where('user_id', Auth::id())
      ->orWhereIn('user_id', $followings)
      ->orderBy('updated_at', 'desc')
      ->with('user')
      ->get();

    return view('tweet.index', compact('tweets'));
  }
}
