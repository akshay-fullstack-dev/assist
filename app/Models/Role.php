<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\User;

class Role extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name',
        'slug',
        'description',
        'level',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */

    public $timestamps = true;

    /**
     * Check user permision for routes.
     * @param $role
     * @return bool
     */
    public function hasRole($role)
    {
        return ($this->role == $role) ? true : false;
    }
    
     /**
   * Many-to-Many relations with the user model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
   public function users()
   {
      return $this->belongsToMany(User::class);
   }
}