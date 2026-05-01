<x-filament-panels::page>
    @php
    $stats = $this->getSummaryStats();
    $isRevenue = $activeTab === 'revenue';

    if ($isRevenue) {
    $trend = $this->getRevenueTrendData();
    $channelMix = $this->getChannelMixData();
    $weekdays = $this->getWeekdaysRevenueData();
    $split = $this->getRevenueSplitData();
    $ratePlan = $this->getRevenueByRatePlanData();
    $categoryMix = $this->getCategoryMixRevenueData();
    $mealPlan = $this->getMealPlanMixData();
    $marketSegmentation = $this->getMarketSegmentationRevenueData();
    } else {
    $trend = $this->getOccupancyTrendData();
    $channelMix = $this->getChannelMixOccupancyData();
    $weekdays = $this->getWeekdaysOccupancyData();
    $categoryMix = $this->getCategoryMixOccupancyData();
    $mealPlan = $this->getMealPlanMixOccupancyData();
    $marketSegmentation = $this->getMarketSegmentationOccupancyData();
    }
    @endphp

    <style>
        .reservation-insights-shell {
            margin: -1.5rem;
            background: #eef3f5;
            color: #333;
            font-family: Arial, Helvetica, sans-serif;
            min-height: calc(100vh - 4rem);
        }

        .insight-header {
            background: #fff;
            border-bottom: 1px solid #d9dee2;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .12);
        }

        .insight-brand {
            display: flex;
            min-height: 46px;
            align-items: center;
        }

        .insight-mark {
            display: grid;
            width: 52px;
            height: 46px;
            place-items: center;
            background: #f57c00;
            color: #fff;
            font-size: 34px;
            font-weight: 400;
            line-height: 1;
        }

        .insight-brand-copy {
            padding-left: 10px;
            line-height: 1.15;
        }

        .insight-brand-copy strong,
        .metric-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .insight-brand-copy span,
        .metric-value,
        .insight-meta {
            color: #777;
            font-size: 11px;
            font-weight: 600;
        }

        .insight-meta {
            margin-left: auto;
            padding-right: 18px;
            text-align: right;
        }

        .metric-strip {
            display: grid;
            grid-template-columns: repeat(5, minmax(100px, 1fr)) auto;
            min-height: 62px;
            align-items: end;
            gap: 0;
            padding: 0 26px;
        }

        .metric-tile {
            border-bottom: 3px solid transparent;
            padding: 13px 8px 10px;
            text-align: center;
        }

        .metric-tile.is-active {
            border-bottom-color: #2fc36c;
        }

        .metric-tile button {
            width: 100%;
            cursor: pointer;
        }

        .metric-label {
            color: #7a8086;
        }

        .metric-value {
            margin-top: 2px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            align-self: center;
            gap: 14px;
            padding-left: 18px;
            white-space: nowrap;
        }

        .sync-text {
            color: #9a9a9a;
            font-size: 12px;
        }

        .get-data {
            border-radius: 2px;
            background: #0f8f20;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            padding: 11px 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
        }

        .insight-content {
            padding: 14px 46px 28px;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }

        .filter-left,
        .filter-right {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .flat-select,
        .flat-chip,
        .segment button {
            min-height: 28px;
            border: 1px solid #cfd6da;
            border-radius: 3px;
            background: #fff;
            color: #3d3d3d;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
        }

        .flat-select {
            min-width: 138px;
            padding: 0 10px;
            text-align: left;
        }

        .flat-chip {
            padding: 0 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
        }

        .segment {
            display: inline-flex;
            overflow: hidden;
            border-radius: 3px;
        }

        .segment button {
            min-width: 92px;
            border-radius: 0;
            border-right: 0;
            padding: 0 14px;
        }

        .segment button:last-child {
            border-right: 1px solid #cfd6da;
        }

        .segment button.is-active {
            background: linear-gradient(#efefef, #dadada);
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, .12);
        }

        .insight-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .chart-panel {
            position: relative;
            min-height: 390px;
            border: 1px solid #d1d8dc;
            border-radius: 4px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .16);
        }

        .chart-panel.full {
            grid-column: 1 / -1;
        }

        .chart-title {
            min-height: 34px;
            padding: 12px 12px 0;
            font-size: 16px;
            font-weight: 400;
            color: #3d3d3d;
        }

        .chart-tools {
            position: absolute;
            top: 49px;
            right: 12px;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chart-tools.top {
            top: 14px;
        }

        .download-mark {
            color: #27ae60;
            font-size: 18px;
            font-weight: 700;
            line-height: 1;
        }

        .menu-mark {
            display: grid;
            gap: 3px;
            width: 14px;
        }

        .menu-mark span {
            display: block;
            height: 2px;
            background: #666;
        }

        .chart-stage {
            height: 326px;
            padding: 4px 18px 12px;
        }

        .chart-stage.tall {
            height: 386px;
        }

        .chart-stage.pie {
            height: 342px;
            padding: 0 26px 18px;
        }

        .chart-note {
            position: absolute;
            bottom: 12px;
            left: 12px;
            color: #444;
            font-size: 11px;
            font-weight: 600;
        }

        .highcharts-credit {
            position: absolute;
            right: 8px;
            bottom: 6px;
            color: #a7a7a7;
            font-size: 9px;
        }

        .empty-ring {
            position: absolute;
            inset: 78px 0 0;
            display: grid;
            place-items: center;
        }

        .empty-ring::before {
            content: '';
            width: min(300px, 55%);
            aspect-ratio: 1;
            border: 1px solid #ddd;
            border-radius: 999px;
        }

        @media (max-width: 1100px) {
            .metric-strip {
                grid-template-columns: repeat(3, minmax(100px, 1fr));
                align-items: stretch;
            }

            .header-actions {
                grid-column: 1 / -1;
                justify-content: flex-end;
                padding: 8px 0 12px;
            }

            .insight-content {
                padding-inline: 16px;
            }
        }

        @media (max-width: 820px) {
            .insight-grid {
                grid-template-columns: 1fr;
            }

            .metric-strip {
                grid-template-columns: repeat(2, minmax(120px, 1fr));
                padding-inline: 10px;
            }

            .insight-meta {
                display: none;
            }

            .segment button {
                min-width: 72px;
            }
        }
    </style>

    <div class="reservation-insights-shell">
        <header class="insight-header">

            <div class="metric-strip">
                <div class="metric-tile">
                    <div class="metric-label">Today's<br>Overview</div>
                </div>

                <div class="metric-tile {{ $isRevenue ? 'is-active' : '' }}">
                    <button type="button" wire:click="setTab('revenue')">
                        <span class="metric-label">Revenue</span>
                        <span class="metric-value">USD {{ $stats['revenue'] }}</span>
                    </button>
                </div>

                <div class="metric-tile {{ ! $isRevenue ? 'is-active' : '' }}">
                    <button type="button" wire:click="setTab('occupancy')">
                        <span class="metric-label">Occupancy</span>
                        <span class="metric-value">{{ $stats['occupancy'] }}</span>
                    </button>
                </div>

                <div class="metric-tile">
                    <div class="metric-label">ARR</div>
                    <div class="metric-value">USD {{ $stats['arr'] }}</div>
                </div>

                <div class="metric-tile">
                    <div class="metric-label">Pre-Booking</div>
                    <div class="metric-value">{{ strtoupper($stats['pre_booking']) }}</div>
                </div>

                <div class="header-actions">
                    <span class="sync-text">Sync Times</span>
                    <button type="button" wire:click="$refresh" class="get-data">GET DATA</button>
                </div>
            </div>
        </header>

        <main class="insight-content">
            <div class="filter-row">
                <div class="filter-left">
                    <button type="button" class="flat-select">
                        {{ $isRevenue ? 'All Revenue' : 'Room Categories' }} ▾
                    </button>
                    <span class="flat-chip">
                        Average {{ $isRevenue ? 'Revenue : USD ' . $stats['arr'] : 'Occupancy : ' . $stats['occupancy'] }}
                    </span>
                </div>

                <div class="filter-right">
                    @unless ($isRevenue)
                    <div class="segment">
                        <button type="button" class="is-active">BY PERCENTAGE</button>
                        <button type="button">BY ROOM NIGHTS</button>
                    </div>
                    @endunless
                </div>
            </div>

            <section class="insight-grid">
                <article class="chart-panel full">
                    <div class="chart-title">{{ $isRevenue ? 'All Revenue Trend' : 'Occupancy Trend' }}</div>
                    <div class="filter-right" style="position: absolute; top: 9px; right: 10px;">
                        <div class="segment">
                            @foreach (['day' => 'DAY', 'week' => 'WEEK', 'month' => 'MONTH'] as $key => $label)
                            <button type="button" wire:click="setPeriod('{{ $key }}')" @class(['is-active'=> $period === $key])>{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div class="chart-tools">
                        <span class="menu-mark" aria-hidden="true"><span></span><span></span><span></span></span>
                    </div>
                    <div class="chart-stage tall">
                        <canvas id="insightTrendChart"></canvas>
                    </div>
                    <span class="highcharts-credit">Highcharts.com</span>
                </article>

                <article class="chart-panel full">
                    <div class="chart-title">{{ $isRevenue ? 'Channel Mix For All Revenue' : 'Channel Mix' }}</div>
                    <div class="chart-tools top">
                        <span class="download-mark">↓</span>
                        <span class="menu-mark" aria-hidden="true"><span></span><span></span><span></span></span>
                    </div>
                    <div class="chart-stage pie">
                        <canvas id="insightChannelChart"></canvas>
                    </div>
                    <span class="highcharts-credit">Highcharts.com</span>
                </article>

                <article class="chart-panel {{ $isRevenue ? '' : 'full' }}">
                    <div class="chart-title">{{ $isRevenue ? 'Weekdays Revenue' : 'Weekdays Occupancy' }}</div>
                    <div class="chart-tools top">
                        <span class="menu-mark" aria-hidden="true"><span></span><span></span><span></span></span>
                    </div>
                    <div class="chart-stage">
                        <canvas id="insightWeekdaysChart"></canvas>
                    </div>
                    <span class="highcharts-credit">Highcharts.com</span>
                </article>

                @if ($isRevenue)
                <article class="chart-panel">
                    <div class="chart-title">All Revenue Split</div>
                    <div class="chart-tools top">
                        <span class="menu-mark" aria-hidden="true"><span></span><span></span><span></span></span>
                    </div>
                    <div class="chart-stage pie">
                        <canvas id="insightSplitChart"></canvas>
                    </div>
                    <div class="chart-note">*Click on the chart to see further breakdown</div>
                    <span class="highcharts-credit">Highcharts.com</span>
                </article>

                <article class="chart-panel">
                    <div class="chart-title">Revenue By Rate Plan</div>
                    <div class="chart-tools top">
                        <span class="menu-mark" aria-hidden="true"><span></span><span></span><span></span></span>
                    </div>
                    <div class="chart-stage pie">
                        <canvas id="insightRatePlanChart"></canvas>
                    </div>
                    <span class="highcharts-credit">Highcharts.com</span>
                </article>
                @endif

                <article class="chart-panel">
                    <div class="chart-title">{{ $isRevenue ? 'Category Mix For All Revenue' : 'Category Mix' }}</div>
                    <div class="chart-tools top">
                        <span class="menu-mark" aria-hidden="true"><span></span><span></span><span></span></span>
                    </div>
                    <div class="chart-stage pie">
                        <canvas id="insightCategoryChart"></canvas>
                    </div>
                    <div class="chart-note">*Click on the chart to see source distribution</div>
                    <span class="highcharts-credit">Highcharts.com</span>
                </article>

                <article class="chart-panel">
                    <div class="chart-title">Meal Plan Mix</div>
                    <div class="chart-tools top">
                        <span class="menu-mark" aria-hidden="true"><span></span><span></span><span></span></span>
                    </div>
                    <div class="chart-stage pie">
                        <canvas id="insightMealPlanChart"></canvas>
                    </div>
                    <div class="chart-note">*Click on the chart to see room category distribution</div>
                    <span class="highcharts-credit">Highcharts.com</span>
                </article>

                @unless ($isRevenue)
                <article class="chart-panel full">
                    <div class="chart-title">Room Utilisation (All Categories)</div>
                    <div class="filter-right" style="position: absolute; top: 9px; right: 10px;">
                        <div class="segment">
                            @foreach (['day' => 'DAY', 'week' => 'WEEK', 'month' => 'MONTH'] as $key => $label)
                            <button type="button" wire:click="setPeriod('{{ $key }}')" @class(['is-active'=> $period === $key])>{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div class="chart-tools" style="top: 44px;">
                        <span class="download-mark">↓</span>
                    </div>
                    <div class="chart-stage tall">
                        <canvas id="insightUtilisationChart"></canvas>
                    </div>
                    <span class="highcharts-credit">Highcharts.com</span>
                </article>
                @endunless

                <article class="chart-panel">
                    <div class="chart-title">Market Segmentation</div>
                    <div class="chart-tools top">
                        <span class="download-mark">↓</span>
                    </div>
                    <div class="chart-stage pie">
                        <canvas id="insightMarketChart"></canvas>
                    </div>
                    <div class="chart-note">*Click on the chart to see Market Segment distribution</div>
                    <span class="highcharts-credit">Highcharts.com</span>
                </article>

                @unless ($isRevenue)
                <article class="chart-panel">
                    <div class="chart-title">Occupancy By Rate Plan</div>
                    <div class="chart-tools top">
                        <span class="menu-mark" aria-hidden="true"><span></span><span></span><span></span></span>
                    </div>
                    <div class="empty-ring"></div>
                    <span class="highcharts-credit">Highcharts.com</span>
                </article>
                @endunless
            </section>
        </main>
    </div>

    @once
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @endpush
    @endonce

    @push('scripts')
    <script>
        (function() {
            const palette = ['#12a8d8', '#ff7f00', '#39e80b', '#9b7b62', '#9e9e9e', '#2b9bea', '#625bd3', '#f45b83', '#d95cec', '#20d983', '#e0d647'];
            const chartData = {
                isRevenue: @json($isRevenue),
                trend: @json($trend),
                channelMix: @json($channelMix),
                weekdays: @json($weekdays),
                split: @json($isRevenue ? $split : null),
                ratePlan: @json($isRevenue ? $ratePlan : null),
                categoryMix: @json($categoryMix),
                mealPlan: @json($mealPlan),
                marketSegmentation: @json($marketSegmentation),
            };

            function cloneData(data) {
                return JSON.parse(JSON.stringify(data));
            }

            const outerLabels = {
                id: 'outerLabels',
                afterDraw(chart, args, options) {
                    if (!options.enabled) {
                        return;
                    }

                    const meta = chart.getDatasetMeta(0);
                    const dataset = chart.data.datasets[0];
                    const values = dataset.data || [];
                    const total = values.reduce((sum, value) => sum + Number(value || 0), 0);

                    if (!total || !meta.data.length) {
                        return;
                    }

                    const ctx = chart.ctx;
                    ctx.save();
                    ctx.font = '700 10px Arial';
                    ctx.lineWidth = 1;

                    meta.data.forEach((arc, index) => {
                        const value = Number(values[index] || 0);
                        if (value <= 0) {
                            return;
                        }

                        const props = arc.getProps(['x', 'y', 'startAngle', 'endAngle', 'outerRadius'], true);
                        const angle = (props.startAngle + props.endAngle) / 2;
                        const startX = props.x + Math.cos(angle) * props.outerRadius;
                        const startY = props.y + Math.sin(angle) * props.outerRadius;
                        const bendX = props.x + Math.cos(angle) * (props.outerRadius + 18);
                        const bendY = props.y + Math.sin(angle) * (props.outerRadius + 18);
                        const isRight = Math.cos(angle) >= 0;
                        const endX = bendX + (isRight ? 18 : -18);
                        const label = `${chart.data.labels[index]}: ${(value / total * 100).toFixed(2)}%`;

                        ctx.strokeStyle = dataset.backgroundColor[index % dataset.backgroundColor.length] || '#12a8d8';
                        ctx.fillStyle = '#2455ff';
                        ctx.beginPath();
                        ctx.moveTo(startX, startY);
                        ctx.lineTo(bendX, bendY);
                        ctx.lineTo(endX, bendY);
                        ctx.stroke();
                        ctx.textAlign = isRight ? 'left' : 'right';
                        ctx.fillText(label, endX + (isRight ? 3 : -3), bendY - 3);
                    });

                    ctx.restore();
                },
            };

            function withColors(data) {
                if (!data || !data.datasets || !data.datasets[0]) {
                    return data;
                }

                data.datasets = data.datasets.map((dataset, index) => ({
                    ...dataset,
                    backgroundColor: dataset.backgroundColor || palette[index % palette.length],
                    borderColor: dataset.borderColor || palette[index % palette.length],
                }));

                return data;
            }

            function asAverageTrend(data) {
                const cloned = cloneData(data);
                const first = cloned.datasets[0];

                if (!first || !first.data.length) {
                    return cloned;
                }

                const values = first.data.map(Number).filter((value) => value > 0);
                const average = values.length ? values.reduce((sum, value) => sum + value, 0) / values.length : 0;

                cloned.datasets.push({
                    label: 'Average Value',
                    data: cloned.labels.map(() => Number(average.toFixed(2))),
                    borderColor: '#6d6d6d',
                    backgroundColor: '#6d6d6d',
                    borderDash: [5, 4],
                    borderWidth: 1,
                    pointRadius: 0,
                    tension: 0,
                });

                return cloned;
            }

            function destroyCanvasChart(id) {
                const canvas = document.getElementById(id);
                if (!canvas || !window.Chart) {
                    return null;
                }

                const existing = Chart.getChart(canvas);
                if (existing) {
                    existing.destroy();
                }

                return canvas;
            }

            function chart(id, type, data, options = {}) {
                const canvas = destroyCanvasChart(id);
                if (!canvas || !data) {
                    return;
                }

                new Chart(canvas, {
                    type,
                    data: withColors(cloneData(data)),
                    options,
                    plugins: [outerLabels],
                });
            }

            function commonLineOptions(yTitle, max) {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    elements: {
                        line: {
                            borderWidth: 2
                        },
                        point: {
                            radius: 2.5,
                            hoverRadius: 4
                        },
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                boxHeight: 8,
                                padding: 14,
                                font: {
                                    size: 11
                                }
                            },
                        },
                        outerLabels: {
                            enabled: false
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#555',
                                maxRotation: 0,
                                autoSkip: true,
                                font: {
                                    size: 11
                                }
                            },
                        },
                        y: {
                            beginAtZero: true,
                            max,
                            grid: {
                                color: '#edf0f2'
                            },
                            title: {
                                display: true,
                                text: yTitle,
                                color: '#666',
                                font: {
                                    size: 12,
                                    weight: '700'
                                }
                            },
                            ticks: {
                                color: '#555',
                                font: {
                                    size: 11
                                }
                            },
                        },
                    },
                };
            }

            function pieOptions(cutout = 0) {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 34,
                            right: 74,
                            bottom: 34,
                            left: 74
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label(context) {
                                    const total = context.dataset.data.reduce((sum, value) => sum + Number(value || 0), 0) || 1;
                                    const percent = Number(context.raw || 0) / total * 100;
                                    return `${context.label}: ${percent.toFixed(2)}%`;
                                },
                            },
                        },
                        outerLabels: {
                            enabled: true
                        },
                    },
                    cutout,
                };
            }

            function barOptions(yTitle) {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                boxHeight: 8,
                                padding: 12,
                                font: {
                                    size: 11
                                }
                            },
                        },
                        outerLabels: {
                            enabled: false
                        },
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#555',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grid: {
                                color: '#edf0f2'
                            },
                            title: {
                                display: true,
                                text: yTitle,
                                color: '#666',
                                font: {
                                    size: 12,
                                    weight: '700'
                                }
                            },
                            ticks: {
                                color: '#555',
                                font: {
                                    size: 11
                                }
                            },
                        },
                    },
                };
            }

            function initReservationInsights() {
                if (!window.Chart) {
                    window.setTimeout(initReservationInsights, 120);
                    return;
                }

                chart(
                    'insightTrendChart',
                    'line',
                    asAverageTrend(chartData.trend),
                    commonLineOptions(chartData.isRevenue ? 'All Revenue (USD)' : 'Occupancy (%)', chartData.isRevenue ? undefined : 30)
                );

                chart('insightChannelChart', 'doughnut', chartData.channelMix, pieOptions('45%'));
                chart('insightWeekdaysChart', 'bar', chartData.weekdays, barOptions(chartData.isRevenue ? 'Source contribution (USD)' : 'Source contribution(%)'));
                chart('insightCategoryChart', 'pie', chartData.categoryMix, pieOptions());
                chart('insightMealPlanChart', 'pie', chartData.mealPlan, pieOptions());
                chart('insightMarketChart', 'pie', chartData.marketSegmentation, pieOptions());

                if (chartData.isRevenue) {
                    chart('insightSplitChart', 'pie', chartData.split, pieOptions());
                    chart('insightRatePlanChart', 'pie', chartData.ratePlan, pieOptions());
                } else {
                    const utilisation = cloneData(chartData.trend);
                    utilisation.datasets = [{
                            label: 'Vacant',
                            data: utilisation.labels.map((label, index) => Math.max(0, 100 - Number(utilisation.datasets[0]?.data[index] || 0))),
                            borderColor: '#ff6384',
                            backgroundColor: '#ff6384',
                            tension: 0.18,
                            fill: false,
                        },
                        {
                            label: 'Occupied',
                            data: utilisation.datasets[0]?.data || [],
                            borderColor: '#9b7b62',
                            backgroundColor: '#9b7b62',
                            tension: 0.18,
                            fill: false,
                        },
                        {
                            label: 'Hold',
                            data: utilisation.labels.map(() => 0),
                            borderColor: '#d95cec',
                            backgroundColor: '#d95cec',
                            tension: 0.18,
                            fill: false,
                        },
                        {
                            label: 'Block for Maintenance',
                            data: utilisation.labels.map(() => 0),
                            borderColor: '#e0d647',
                            backgroundColor: '#e0d647',
                            tension: 0.18,
                            fill: false,
                        },
                        {
                            label: 'Block for Booking',
                            data: utilisation.datasets.slice(1).reduce((series, dataset) => {
                                dataset.data.forEach((value, index) => {
                                    series[index] = (series[index] || 0) + Number(value || 0);
                                });

                                return series;
                            }, utilisation.labels.map(() => 0)),
                            borderColor: '#2b9bea',
                            backgroundColor: '#2b9bea',
                            tension: 0.18,
                            fill: false,
                        },
                    ];
                    chart('insightUtilisationChart', 'line', utilisation, commonLineOptions('Occupancy (%)', 125));
                }
            }

            initReservationInsights();
            document.addEventListener('livewire:navigated', initReservationInsights);
            document.addEventListener('livewire:updated', initReservationInsights);
        })();
    </script>
    @endpush
</x-filament-panels::page>