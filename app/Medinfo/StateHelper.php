<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 12.11.2018
 * Time: 13:44
 */

namespace App\Medinfo;

use App\UserSetting;
use Illuminate\Http\Request;

class StateHelper
{
    // Сохранение настроек отображения списка документов
    public static function saveUserDocListState(Request $request, $user)
    {
        $last_filter_mode = UserSetting::firstOrCreate(['user_id' => $user->id, 'name' => 'filter_mode']);
        $last_filter_mode->value = $request->filter_mode;
        $last_filter_mode->save();
        $last_ou = UserSetting::firstOrCreate(['user_id' => $user->id, 'name' => 'ou']);
        $last_ou->value = $request->ou;
        $last_ou->save();
        $last_mf = UserSetting::firstOrCreate(['user_id' => $user->id, 'name' => 'mf']);
        $last_mf->value = $request->mf;
        $last_mf->save();
        $last_monitorings = UserSetting::firstOrCreate(['user_id' => $user->id, 'name' => 'monitorings']);
        $last_monitorings->value = $request->monitorings;
        $last_monitorings->save();
        $last_forms = UserSetting::firstOrCreate(['user_id' => $user->id, 'name' => 'forms']);
        $last_forms->value = $request->forms;
        $last_forms->save();
        $last_periods = UserSetting::firstOrCreate(['user_id' => $user->id, 'name' => 'periods']);
        $last_periods->value = $request->periods;
        $last_periods->save();
        $last_states = UserSetting::firstOrCreate(['user_id' => $user->id, 'name' => 'states']);
        $last_states->value = $request->states;
        $last_states->save();
        $last_states = UserSetting::firstOrCreate(['user_id' => $user->id, 'name' => 'dtypes']);
        $last_states->value = $request->dtypes;
        $last_states->save();
        $last_states = UserSetting::firstOrCreate(['user_id' => $user->id, 'name' => 'filleddocs']);
        $last_states->value = $request->filled;
        $last_states->save();
    }
    // Получение настроек пользователя
    public static function getUserLastState($user)
    {
        $sets = [];
        $settings = UserSetting::OfUser($user)->get();
        foreach ($settings as $setting) {
            $sets[$setting->name] = $setting->value;
        }
        return $sets;
    }

}