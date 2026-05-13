<?php namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model {
    use HasUuids;
    protected $fillable = ['created_by', 'title', 'body', 'image', 'is_urgent', 'is_active'];
    protected function casts(): array { 
        return [
            'is_active' => 'boolean',
            'is_urgent' => 'boolean'
        ]; 
    }
    public function creator() { return $this->belongsTo(User::class,'created_by'); }
    public function scopeActive($q) { return $q->where('is_active',true); }
    public function isReadBy(string $userId): bool {
        return ReadStatus::where('user_id',$userId)->where('item_id',$this->id)->where('item_type','announcement')->exists();
    }
}
