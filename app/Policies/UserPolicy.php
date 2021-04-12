<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function viewAny(User $user, $for = '')
    {
        $permissions = $user->role()->first()->permissions;
        if (count($permissions) > 0 && ($permissions[0] == 'all' || in_array('vu', $permissions)))
            return true;
        else {
            if ($for == 'can') //is in blade , only return true false
                return false;

            return abort(403, 'متاسفانه اجازه مشاهده کاربران را ندارید');
        }
    }

    public function createAny(User $user, $for = '')
    {
        $permissions = $user->role()->first()->permissions;
        if (count($permissions) > 0 && ($permissions[0] == 'all' || in_array('cu', $permissions)))
            return true;
        else {
            if ($for == 'can') //is in blade , only return true false
                return false;

            return abort(403, 'متاسفانه اجازه ساخت کاربر را ندارید');
        }
    }

    public function deleteAny(User $user, $for = '')
    {
        $permissions = $user->role()->first()->permissions;
        if (count($permissions) > 0 && ($permissions[0] == 'all' || in_array('du', $permissions)))
            return true;
        else {
            if ($for == 'can') //is in blade , only return true false
                return false;

            return abort(403, 'متاسفانه اجازه حذف کاربران را ندارید');
        }
    }

    public function editAny(User $user, $for = '')
    {
        $permissions = $user->role()->first()->permissions;
        if (count($permissions) > 0 && ($permissions[0] == 'all' || in_array('eu', $permissions)))
            return true;
        else {
            if ($for == 'can') //is in blade , only return true false
                return false;

            return abort(403, 'متاسفانه اجازه ویرایش کاربران را ندارید');
        }
    }

    public function create(User $user)
    {
        if (in_array('cu', $user->role()->first()->permissions))
            return true;
        else return false;

    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User $user
     * @param  \App\User $model
     * @return mixed
     */
    public function view(User $user, User $model)
    {
        if (in_array('vu', $user->role()->first()->permissions))
            return true;
        else return false;
    }


    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User $user
     * @param  \App\User $model
     * @return mixed
     */
    public function edit(User $user, User $model)
    {
        if (in_array('eu', $user->role()->first()->permissions))
            return true;
        else return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User $user
     * @param  \App\User $model
     * @return mixed
     */
    public function delete(User $user, User $model)
    {
        if (in_array('ru', $user->role()->first()->permissions))
            return true;
        else return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User $user
     * @param  \App\User $model
     * @return mixed
     */
    public function restore(User $user, User $model)
    {
//        $model->restore();
        if (in_array('cu', $user->role()->first()->permissions))
            return true;
        else return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User $user
     * @param  \App\User $model
     * @return mixed
     */
    public function forceDelete(User $user, User $model)
    {
        //
    }
}
