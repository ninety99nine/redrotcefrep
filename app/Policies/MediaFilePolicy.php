<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MediaFile;
use App\Enums\UploadFolderName;

class MediaFilePolicy extends BasePolicy
{
    /**
     * Grant all permissions to super admins who have roles not tied to any store.
     *
     * @param User $user
     * @param string $ability
     * @return bool|null
     */
    public function before(User $user, string $ability): bool|null
    {
        return $this->authService->isSuperAdmin($user) ?: null;
    }

    /**
     * Determine whether the user can view any media files.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the media file.
     *
     * @param User $user
     * @param MediaFile $mediaFile
     * @return bool
     */
    public function view(User $user, MediaFile $mediaFile): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create media files.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isAllowed($user);
    }

    /**
     * Determine whether the user can delete the media file.
     *
     * @param User $user
     * @param MediaFile $mediaFile
     * @return bool
     */
    public function delete(User $user, MediaFile $mediaFile): bool
    {
        return $this->isAllowed($user, $mediaFile);
    }

    /**
     * Determine whether the user can download the media file.
     *
     * @param User $user
     * @param MediaFile $mediaFile
     * @return bool
     */
    public function download(User $user, MediaFile $mediaFile): bool
    {
        return $this->isAllowed($user, $mediaFile);
    }

    /**
     * Determine whether the user can delete any media files.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user is allowed to perform the action.
     *
     * @param User $user
     * @param ?MediaFile $mediaFile
     * @return bool
     */
    private function isAllowed(User $user, ?MediaFile $mediaFile = null): bool
    {
        $uploadFolderName = UploadFolderName::tryFrom($mediaFile?->type ?? request()->input('upload_folder_name'));

        if($uploadFolderName == UploadFolderName::PROFILE_PHOTO) {
            $userId = request()->input('user_id');
            return $user->id == $userId;
        }else if($uploadFolderName == UploadFolderName::ORDER_COMMENT_PHOTO) {
            return $this->isStoreUserWithPermission($user, 'manage orders');
        }else if($uploadFolderName == UploadFolderName::PRODUCT_PHOTO) {
            return $this->isStoreUserWithPermission($user, 'manage products');
        }else if($uploadFolderName == UploadFolderName::CATEGORY_PHOTO) {
            return $this->isStoreUserWithPermission($user, 'manage products');
        }else if(in_array(
            $uploadFolderName,
            [
                UploadFolderName::STORE_LOGO,
                UploadFolderName::DESIGN_CARD_PHOTO,
                UploadFolderName::STORE_PAYMENT_METHOD_LOGO,
                UploadFolderName::STORE_PAYMENT_METHOD_PHOTO,
            ]
        )) {
            return $this->isStoreUserWithPermission($user, 'manage store');
        }else{
            return false;
        }
    }
}
