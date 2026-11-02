<?php

namespace App\Http\Services;

use App\Http\Resources\UserResource;
use App\Models\CardTransaction;
use App\Models\MPESATransaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService extends Service
{
	/*
     * Get All Users
     */
	public function index($request)
	{
		if ($request->filled("idAndName")) {
			$userQuery = User::select("id", "name");

			$users = $userQuery
				->orderBy("id", "DESC")
				->get();

			return $users;
		}

		$query = new User;

		$query = $this->search($query, $request);

		$users = $query
			->orderby("id", "DESC")
			->paginate();

		return $users;
	}

	/*
	* Store User
	*/ 
	public function store($request)
	{
		$user = new User;
		$user->name = $request->name;
		$user->email = $request->email;
		$user->phone = $request->phone;
		$user->password = Hash::make($request->email);
		$user->type = $request->type;
		$saved = $user->save();

		return [$saved, "User Created Successfully", $user];
	}

	/**
	 * Display the specified resource.
	 *
	 */
	public function show($id)
	{
		return User::findOrFail($id);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 */
	public function update($request, $id)
	{
		/* Update profile */
		$user = User::findOrFail($id);

		$user->name = $request->input('name', $user->name);
		$user->phone = $request->input('phone', $user->phone);
		$user->email = $request->input('email', $user->email);

		if ($request->filled('password')) {
			$user->password = Hash::make($request->input('password'));
		}

		$saved = $user->save();

		return [$saved, "User Updated", $user];
	}

	/*
     * Soft Delete Service
     */
	public function destory($id)
	{
		$user = User::findOrFail($id);

		$deleted = $user->delete();

		return [$deleted, $user->name . " deleted"];
	}

	/*
     * Force Delete Service
     */
	public function forceDestory($id)
	{
		$user = User::findOrFail($id);

		// Get old thumbnail and delete it
		$oldThumbnail = substr($user->thumbnail, 9);

		Storage::disk("public")->delete($oldThumbnail);

		$deleted = $user->delete();

		return [$deleted, $user->name . " deleted"];
	}

	/**
	 * Get Auth.
	 *
	 */
	public function auth()
	{
		if (auth("sanctum")->check()) {

			$auth = auth('sanctum')->user();

			return new UserResource($auth);
		} else {
			return response(["message" => "Not Authenticated"], 401);
		}
	}

	/*
     * Search
     */
	public function search($query, $request)
	{
		$name = $request->input("name");

		if ($request->filled("name")) {
			$query = $query->where("name", "LIKE", "%" . $name . "%");
		}

		$type = $request->input("type");

		if ($request->filled("type")) {
			$query = $query->where("type", $type);
		}

		return $query;
	}

	/*
	 * Update the signed in user's name and email. A new email has to be
	 * verified again.
	 */
	public function updateProfile(User $user, array $attributes): void
	{
		$user->fill($attributes);

		if ($user->isDirty('email')) {
			$user->email_verified_at = null;
		}

		$user->save();
	}

	/*
	 * Set the signed in user's password (hashed by the model's cast).
	 */
	public function updatePassword(User $user, string $password): void
	{
		$user->update(['password' => $password]);
	}

	/*
	 * Delete the signed in user's account.
	 */
	public function deleteAccount(User $user): void
	{
		$user->delete();
	}
}
