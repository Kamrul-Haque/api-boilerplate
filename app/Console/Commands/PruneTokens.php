<?php

namespace App\Console\Commands;

use App\Models\PersonalAccessToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PruneTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:prune
                            {--chunk=1000 : Number of rows to delete per batch}
                            {--dry-run : Count the tokens that would be pruned without deleting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune expired and stale revoked authentication tokens';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();
        $chunkSize = max(1, (int) $this->option('chunk'));
        $retentionDays = max(1, (int) config('token.revoked_retention_days'));
        $revokedCutoff = $now->copy()->subDays($retentionDays);

        $query = PersonalAccessToken::query()
            ->where(function ($query) use ($now, $revokedCutoff) {
                $query->where('expires_at', '<=', $now)
                    ->orWhere(function ($query) use ($revokedCutoff) {
                        $query->whereNotNull('revoked_at')
                            ->where('revoked_at', '<=', $revokedCutoff);
                    });
            });

        if ($this->option('dry-run')) {
            $this->info($query->count().' token(s) would be pruned.');

            return self::SUCCESS;
        }

        $pruned = 0;

        $query->chunkById($chunkSize, function ($tokens) use (&$pruned) {
            $ids = $tokens->pluck('id');

            PersonalAccessToken::whereKey($ids)->delete();

            $ids->each(fn ($id) => Cache::forget(PersonalAccessToken::cacheKeyForId($id)));

            $pruned += $ids->count();
        });

        $this->info("Pruned {$pruned} token(s).");

        return self::SUCCESS;
    }
}
