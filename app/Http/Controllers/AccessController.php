<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\User;
use App\Models\Store_members;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class AccessController extends Controller
{
    static function access_owner(int $store_id)
    {
        //check data
        if (!Store::find($store_id)) {
            return false;
        }

        //check is owner
        $user_have_role = User::with('customers', 'roles')->find(Auth::user()->id);
        $role_in_store = Role::where('store_id', '=', $store_id)->get();
        foreach ($user_have_role['roles'] as $value) {
            if ($role_in_store[0]->id == $value->id) {
                return true;
                break;
            }
        }
        return false;
    }

    static function access_staff(int $store_id)
    {
        //check data
        if (!Store::find($store_id)) {
            return false;
        }
        //check is staff
        $user_have_role = User::with('customers', 'roles')->find(Auth::user()->id);
        $role_in_store = Role::where('store_id', '=', $store_id)->get();
        foreach ($user_have_role['roles'] as $value) {
            if ($role_in_store[1]->id == $value->id) {
                return true;
                break;
            }
        }
        return false;
    }

    static function access_member(int $store_id)
    {
        //check data
        if (!Store::find($store_id)) {
            return false;
        }
        //check is members
        $store_members = Store_members::where('store_id', '=', $store_id)
            ->where('users_id', '=', Auth::user()->id)
            ->where('status_id', '=', 10)
            ->first();

        if ($store_members) {
            return true;
        }
        return false;
    }
}
