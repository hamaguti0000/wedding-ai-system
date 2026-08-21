<?php

namespace App\Services;

use App\Models\GalleryPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class RandomGalleryPhotoPicker
{
    public function pick(int $limit = 10, ?User $user = null): Collection
    {
        try {
            if (! Schema::hasTable('gallery_photos')) {
                return collect();
            }

            $picked = collect();
            $excludeIds = collect();

            if ($user) {
                $this->appendBucket($picked, $excludeIds, $limit, function (Builder $query) use ($user) {
                    $query->whereHas('taggedUsers', fn (Builder $tagQuery) => $tagQuery->whereKey($user->id));
                });

                if ($this->hasGroupContext($user)) {
                    $this->appendBucket($picked, $excludeIds, $limit, function (Builder $query) use ($user) {
                        $this->applyGroupRelatedFilter($query, $user);
                    });
                }
            }

            $this->appendBucket($picked, $excludeIds, $limit, function (Builder $query) {
                $query->where(function (Builder $tagQuery) {
                    $tagQuery->whereHas('taggedUsers')
                        ->orWhereHas('taggedGroups');
                });
            });

            $this->appendBucket($picked, $excludeIds, $limit);

            return $picked->take($limit)->values();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function appendBucket(Collection $picked, Collection $excludeIds, int $limit, ?callable $scope = null): void
    {
        $remaining = $limit - $picked->count();
        if ($remaining <= 0) {
            return;
        }

        $query = $this->baseQuery();

        if ($excludeIds->isNotEmpty()) {
            $query->whereNotIn('id', $excludeIds->all());
        }

        if ($scope) {
            $scope($query);
        }

        $photos = $query->inRandomOrder()->limit($remaining)->get();
        if ($photos->isEmpty()) {
            return;
        }

        $picked->push(...$photos);
        $excludeIds->push(...$photos->pluck('id'));
    }

    private function baseQuery(): Builder
    {
        return GalleryPhoto::query()
            ->where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('file_path');
    }

    private function hasGroupContext(User $user): bool
    {
        $profile = $user->guestProfile;

        if ($profile && filled($profile->guest_side) && filled($profile->relationship)) {
            return true;
        }

        return Schema::hasTable('guest_group_members')
            && $user->guestGroups()->exists();
    }

    private function applyGroupRelatedFilter(Builder $query, User $user): void
    {
        $profile = $user->guestProfile;
        $hasGroupTables = Schema::hasTable('guest_groups')
            && Schema::hasTable('guest_group_members')
            && Schema::hasTable('gallery_photo_group_taggings');
        $groupIds = $hasGroupTables
            ? $user->guestGroups()->pluck('guest_groups.id')
            : collect();

        $query->where(function (Builder $relatedQuery) use ($groupIds, $profile, $hasGroupTables) {
            if ($hasGroupTables && $groupIds->isNotEmpty()) {
                $relatedQuery->whereHas('taggedGroups', fn (Builder $groupQuery) => $groupQuery->whereIn('guest_groups.id', $groupIds))
                    ->orWhereHas('taggedUsers.guestGroups', fn (Builder $groupQuery) => $groupQuery->whereIn('guest_groups.id', $groupIds));
            }

            if ($profile && filled($profile->guest_side) && filled($profile->relationship)) {
                $method = $hasGroupTables && $groupIds->isNotEmpty() ? 'orWhere' : 'where';
                $relatedQuery->{$method}(function (Builder $profileQuery) use ($profile, $hasGroupTables) {
                    if ($hasGroupTables) {
                        $profileQuery->whereHas('taggedGroups', function (Builder $groupQuery) use ($profile) {
                            $groupQuery->where('guest_side', $profile->guest_side)
                                ->where('relationship', $profile->relationship);
                        })->orWhereHas('taggedUsers.guestProfile', function (Builder $guestQuery) use ($profile) {
                            $guestQuery->where('guest_side', $profile->guest_side)
                                ->where('relationship', $profile->relationship);
                        });

                        return;
                    }

                    $profileQuery->whereHas('taggedUsers.guestProfile', function (Builder $guestQuery) use ($profile) {
                        $guestQuery->where('guest_side', $profile->guest_side)
                            ->where('relationship', $profile->relationship);
                    });
                });
            }
        });
    }
}
