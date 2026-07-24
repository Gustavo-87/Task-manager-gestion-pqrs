<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PqrTag extends Model { protected $fillable=['name','color']; public function pqrs(){return $this->belongsToMany(Pqr::class);} }
