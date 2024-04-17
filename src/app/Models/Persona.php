<?php

namespace Src\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Persona
{
  use HasFactory;

  protected $table = 'personas';
  protected $primarykey = 'cedula';

  protected $fillable = [
    'per_nombres',
    'per_apellidos',
    'per_fechaNacimiento',
    'per_estado',
    'per_areaTrabajo',
    'per_correo',
  ];
}
