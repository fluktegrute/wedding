# Wedding — Application de partage de photos de mariage

## Contexte produit

Application permettant aux invités d'un mariage de partager leurs photos entre eux.

- Les invités créent un compte, puis peuvent déposer leurs photos.
- Ils peuvent ajouter un commentaire optionnel à une photo (au dépôt ou après).
- Ils peuvent consulter les photos déposées par les autres invités.
- Ils peuvent réagir aux photos avec des emojis (une réaction par photo et par utilisateur).
- Un espace d'administration est réservé aux utilisateurs `is_admin` :
  - mettre des photos en avant (`is_featured`) ;
  - sélectionner des photos à exporter vers Google Drive (`is_synced_to_gdrive`, `gdrive_file_id`, `gdrive_synced_at`).

## Stack technique

- Laravel 13 (starter kit officiel Livewire), PHP 8.4.
- Livewire 4 + Flux UI (bibliothèque de composants officielle Livewire).
- Livewire Blaze (optimisation des composants Blade).
- Laravel Fortify pour l'authentification (dont 2FA — colonnes `two_factor_*` gérées par la migration du package, pas par nos migrations).
- Laravel Chisel (dev — nettoyage de code/fichiers/dépendances inutilisés).
- Base de données : MariaDB (voir `.env`, `DB_CONNECTION=mariadb`).
- Pint (preset `laravel`) pour le style de code, Larastan niveau 7 pour l'analyse statique, Pest pour les tests.

## Modèle de données

Source de vérité : `database/schema.dbml`. Les migrations et modèles Eloquent (`app/Models/`) doivent rester synchronisés avec ce fichier — le mettre à jour en premier en cas d'évolution du schéma.

- `users` — `is_admin` gère les droits d'administration. Volontairement absent de `#[Fillable]` sur le modèle `User` pour éviter toute élévation de privilèges par mass-assignment.
- `photos` — appartient à un `user` (invité qui a uploadé). Champs de mise en avant et de synchronisation Google Drive réservés aux admins (non fillable par l'utilisateur standard).
- `comments` — appartient à une `photo` et à un `user`.
- `reactions` — appartient à une `photo` et à un `user`, unique par couple `(photo_id, user_id)`.

Convention modèles : suivre le style de `app/Models/User.php` (attributs `#[Fillable]`/`#[Hidden]`, docblocks `@property`, méthode `casts()`, relations typées avec generics PHPDoc `@return BelongsTo<X, $this>`).

## Checklist des fonctionnalités à implémenter

### Authentification & comptes
- [x] Inscription / connexion (Fortify, starter kit)
- [x] Gestion du profil, sécurité, apparence (starter kit)
- [x] Adapter l'inscription au contexte mariage (ex. libellés, page d'accueil dédiée)

### Espace invité
- [x] Formulaire de dépôt de photo (upload image + nom de fichier original + description optionnelle)
- [x] Stockage des fichiers (local ou S3 — `image_path`)
- [x] Galerie listant les photos de tous les invités
- [x] Page/vue détail d'une photo
- [x] Ajout de commentaire sur une photo
- [x] Ajout/retrait de réaction emoji sur une photo (une réaction par utilisateur et par photo)
- [x] Affichage des commentaires et réactions sur une photo

### Espace administration
- [x] Middleware/gate d'accès réservé aux utilisateurs `is_admin` (middleware `admin`, voir `app/Http/Middleware/EnsureUserIsAdmin.php`)
- [x] Mise en avant d'une photo (`is_featured`) — `App\Livewire\Admin\Photos`
- [x] Sélection de photos et export vers Google Drive — toggle `is_synced_to_gdrive`/`gdrive_synced_at` via `App\Services\GoogleDriveExporter`
- [x] Intégration Google Drive (auth API, upload réel, renseignement de `gdrive_file_id`) — compte de service (JWT bearer + upload multipart REST, sans SDK) dans `App\Services\GoogleDriveClient`, credentials dans `config/services.php` (`GOOGLE_DRIVE_CLIENT_EMAIL`/`GOOGLE_DRIVE_PRIVATE_KEY`/`GOOGLE_DRIVE_FOLDER_ID` en `.env`)
- [x] Vue listant les photos synchronisées / à synchroniser (filtres dans `resources/views/livewire/admin/photos.blade.php`)

### Transverse
- [x] Policies (`PhotoPolicy`, `CommentPolicy`, `ReactionPolicy`) pour les autorisations (auteur vs admin)
- [x] Factories & seeders pour les nouveaux modèles (Photo, Comment, Reaction)
- [x] Tests Pest (modèles, policies, flux d'upload, réactions, espace admin)
- [x] Modération : suppression d'une photo (`App\Livewire\Photos\Show::deletePhoto`) ou d'un commentaire (`deleteComment`) par un admin ou son auteur, avec confirmation sur la vue détail
