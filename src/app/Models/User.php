<?php

namespace Src\app\Models;

require_once '../../../vendor/autoload.php';

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User
{

  use HasFactory;

  protected $tabla = 'usuarios';
  protected $primarykey = 'id_user';

  protected $fillable = [
    'usu_user',
    'usu_password',
    'usu_rol',
    'usu_registro',
    'usu_delete',
    'cedula',
  ];

  protected $hiden = [
    'usu_password',
    'remember_token',
  ];

  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datatime',
      'usu_password' => 'hashed',
    ];
  }
}
