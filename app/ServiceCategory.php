<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    private $active_service = "1";
    const active_service_catagory = '1';
    protected $table = 'service_categories';
    protected $fillable = ['cat_name', 'image', 'parent_id', 'status'];

    public function Services()
    {
        return $this->hasMany('App\service', 'cat_id', 'id')->where(['status' => $this->active_service]);
    }

    public function ServicesWithCon($ids)
    {
        return $this->hasMany('App\service', 'cat_id', 'id')->whereIn('id', $ids);
    }
}
