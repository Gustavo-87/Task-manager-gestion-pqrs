<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PqrReply extends Model {
    protected $fillable = ['pqr_id', 'user_id', 'body', 'is_draft', 'attachments', 'sent_at'];
    protected function casts(): array { return ['is_draft' => 'boolean', 'attachments' => 'array', 'sent_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
    public function pqr() { return $this->belongsTo(Pqr::class); }
}
