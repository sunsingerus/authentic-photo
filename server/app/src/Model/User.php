<?php
namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Common\Helper;
use App\Common\Auth;

/**
 * Class User
 *
 * @property integer        $id
 * @property string         $email
 * @property string         $full_name
 * @property string         $password
 * @property string         $password_reset_token
 * @property integer        $role_id
 * @property integer        $created_by
 * @property integer        $updated_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property integer        $status
 * @property-read Role      $role
 *
 * @package App\Model
 */
final class User extends BaseModel
{
    use SoftDeletes;

    const STATUS_BLOCKED     = 0;
    const STATUS_ACTIVE      = 1;
    const STATUS_WAIT        = 2;

    const ROLE_ADMIN         = 1;
    const ROLE_USER          = 2;

    const EXPIRE_RESET_TOKEN = 3600;

    protected $table = 'users';

    protected $fillable = [
        'full_name',
        'email',
        'role_id',
        'status'
    ];

    protected $hidden = [
        'password',
        'password_reset_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function role()
    {
        return $this->hasOne('App\Model\Role', 'id', 'role_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accessTokens()
    {
        return $this->hasMany('App\Model\AccessToken', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refreshTokens()
    {
        return $this->hasMany('App\Model\RefreshToken', 'user_id', 'id');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeCurrentUser($query)
    {
        $user = Auth::getUser();

        if ($user) {
            if ($user->role_id == self::ROLE_ADMIN) {
                return $query;
            }

            $query->where('id', $user->id);
        } else {
            $query->where('id', 0);
        }

        return $query;
    }

    /**
     * Create new User instance and save it
     *
     * @param $attributes
     * @param $password
     * @return User|null
     */
    public static function create($attributes, $password)
    {
        $user = new self($attributes);
        $user->setPassword($password);
        if (!$user->save()) {
            return null;
        }

        return $user;
    }


    /**
     * @param $email
     *
     * @return bool
     */
    public static function exist($email)
    {
        return self::where('email', $email)->count() > 0;
    }

    /**
     * @param string $email
     *
     * @return User|null
     */
    public static function findUserByEmail($email)
    {
        return self::where('email', $email)->where('status', self::STATUS_ACTIVE)->first();
    }

    /**
     * @param string $resetToken
     *
     * @return User|null
     */
    public static function findByPasswordResetToken($resetToken)
    {
        if (!self::isPasswordResetTokenValid($resetToken)) {
            return null;
        }

        return self::where('password_reset_token', $resetToken)->where('status', self::STATUS_ACTIVE)->first();
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire    = self::EXPIRE_RESET_TOKEN;
        return $timestamp + $expire >= time();
    }

    /**
     * @void
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Helper::generateRandomString().'_'.time();
    }

    /**
     * @void
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        // we need to invalidate tokens when changing password
        AccessToken::where('user_id', $this->id)->delete();
        RefreshToken::where('user_id', $this->id)->delete();

        $this->password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);
    }
}
