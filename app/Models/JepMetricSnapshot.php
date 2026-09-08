<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JepMetricSnapshot extends Model
{
    protected $fillable = [
        'organization_id', 'total_political_prisoners', 'total_political_prisoners_trend', 'women', 'women_trend', 'seriously_ill', 'seriously_ill_trend',
        'foreign_or_dual_nationality', 'foreign_or_dual_nationality_trend', 'releases', 'releases_trend', 'releases_period_start_month',
        'releases_period_start_day', 'releases_period_start_year', 'releases_period_end_month',
        'releases_period_end_day', 'releases_period_end_year', 'active_retired_officials',
        'new_detentions', 'missing_location', 'deaths_in_custody', 'deaths_period_start_month', 'deaths_period_start_day',
        'deaths_period_start_year', 'deaths_period_end_month', 'deaths_period_end_day', 'deaths_period_end_year',
        'detentions_methodology_note', 'monthly_alert_title', 'monthly_alert_excerpt', 'monthly_alert_x_url',
        'featured_indicator_title', 'featured_indicator_text', 'featured_indicator_instagram_url', 'featured_indicator_x_url', 'featured_indicator_read_more_url', 'featured_indicator_image_path',
        'data_date', 'valid_from', 'valid_until', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'total_political_prisoners' => 'integer', 'total_political_prisoners_trend' => 'decimal:2', 'women' => 'integer', 'women_trend' => 'decimal:2', 'seriously_ill' => 'integer', 'seriously_ill_trend' => 'decimal:2',
            'foreign_or_dual_nationality' => 'integer', 'foreign_or_dual_nationality_trend' => 'decimal:2', 'releases' => 'integer', 'releases_trend' => 'decimal:2',
            'releases_period_start_month' => 'integer', 'releases_period_start_day' => 'integer',
            'releases_period_start_year' => 'integer', 'releases_period_end_month' => 'integer',
            'releases_period_end_day' => 'integer', 'releases_period_end_year' => 'integer',
            'active_retired_officials' => 'integer', 'new_detentions' => 'integer',
            'missing_location' => 'integer', 'deaths_in_custody' => 'integer',
            'deaths_period_start_month' => 'integer', 'deaths_period_start_day' => 'integer', 'deaths_period_start_year' => 'integer',
            'deaths_period_end_month' => 'integer', 'deaths_period_end_day' => 'integer', 'deaths_period_end_year' => 'integer',
            'data_date' => 'date', 'valid_from' => 'datetime', 'valid_until' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function vulnerableGroups(): HasMany { return $this->hasMany(JepVulnerableGroup::class, 'snapshot_id')->orderBy('sort_order'); }
    public function detentionCenters(): HasMany { return $this->hasMany(JepDetentionCenter::class, 'snapshot_id')->orderBy('sort_order'); }
    public function deathCustodyDistribution(): HasMany { return $this->hasMany(JepDeathCustodyDistribution::class, 'snapshot_id')->orderBy('sort_order'); }
    public function scopeCurrent(Builder $query): Builder { return $query->whereNull('valid_until'); }
}
