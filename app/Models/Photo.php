<?php

namespace App\Models;

use Database\Factories\PhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $user_id L'invité qui a uploadé la photo
 * @property string $image_path Chemin de stockage local ou S3
 * @property string $original_filename
 * @property string|null $description Commentaire optionnel ajouté lors du dépôt de la photo
 * @property bool $is_featured Permet aux admins de mettre la photo en avant
 * @property bool $is_synced_to_gdrive
 * @property string|null $gdrive_file_id ID du fichier sur Google Drive une fois exporté
 * @property Carbon|null $gdrive_synced_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $image_url URL publique de la photo sur le disque 'public'
 */
#[Fillable(['image_path', 'original_filename', 'description'])]
class Photo extends Model
{
    /** @use HasFactory<PhotoFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_synced_to_gdrive' => 'boolean',
            'gdrive_synced_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Photo $photo): void {
            Storage::disk('public')->delete($photo->image_path);
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<Reaction, $this>
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::disk('public')->url($this->image_path),
        );
    }
}
