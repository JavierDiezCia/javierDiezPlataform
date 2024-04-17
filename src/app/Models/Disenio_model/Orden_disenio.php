<?php

namespace Src\app\Models\Disenio_model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Orden_disenio
{
  use HasFactory;

  protected $table = 'orden_disenio';
  protected $primarykey = 'od_id';

  protected $fillable = [
    'od_responsable',
    'od_comercial',
    'od_detalle',
    'od_cliente',
    'od_fechaRegistro',
    'od_estado',
  ];

  public function casts(): array
  {
    return [
      'od_estado' => 'enum',
      'od_fechaRegistro' => 'datetime',
    ];
  }
}
