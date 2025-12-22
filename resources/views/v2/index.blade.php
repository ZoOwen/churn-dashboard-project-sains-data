@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="mb-1">Dashboard Churn Analysis</h2>
    <p class="text-muted mb-4">Pilih dataset CSV untuk dianalisa</p>

    {{-- Dataset Selector --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <select id="datasetSelect" class="form-select w-auto">
            @foreach($datasets as $ds)
                <option value="{{ $ds->stored_filename }}">{{ $ds->name }}</option>
            @endforeach
        </select>
        <button id="processBtn" class="btn btn-primary">
            Proses Dataset
        </button>
    </div>

    {{-- STATS --}}
    <div id="stats" class="stats-row d-none"></div>

    {{-- CHARTS --}}
    <div id="dashboard" class="d-none">

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="chart-box">
                    <h6 class="chart-title">Customer Churn Overview</h6>
                    <div id="pieChart"></div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="chart-box">
                    <h6 class="chart-title">Churn Rate by Contract</h6>
                    <div id="barChart"></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="chart-box">
                    <h6 class="chart-title">Top Risk Factors</h6>
                    <div id="riskChart"></div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="chart-box">
                    <h6 class="chart-title">Tenure Trend</h6>
                    <div id="tenureChart"></div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
@push('styles')
<style>
/* === STATS === */
.stats-row {
    display: flex;
    gap: 16px;
    margin-bottom: 32px;
}

.stat-card {
    flex: 1;
    background: white;
    padding: 20px;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,.06);
}

.stat-value {
    font-size: 32px;
    font-weight: 800;
    margin-top: 6px;
}

.stat-label {
    font-size: 13px;
    color: #6b7280;
}

/* === CHART === */
.chart-box {
    background: white;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 10px 28px rgba(0,0,0,.06);
}

.chart-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
}

/* Plotly container fix */
#pieChart,
#barChart,
#riskChart,
#tenureChart {
    height: 360px;
}
</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>
<script src="https://cdn.plot.ly/plotly-2.30.0.min.js"></script>

<script>
let rawData = [];

document.getElementById('processBtn').addEventListener('click', () => {
    const file = document.getElementById('datasetSelect').value;
    const url = '/storage/datasets/' + file;

    Papa.parse(url, {
        download: true,
        header: true,
        dynamicTyping: true,
        complete: (res) => {
            rawData = res.data.filter(r => r.TotalCharges);
            renderDashboard();
        }
    });
});

function renderDashboard() {
    const total = rawData.length;
    const churn = rawData.filter(r => r.Churn === 'Yes').length;
    const active = total - churn;
    const rate = ((churn / total) * 100).toFixed(2);

    // === STATS ===
    document.getElementById('stats').innerHTML = `
        <div class="stat-card">
            <div class="stat-label">Total Customers</div>
            <div class="stat-value">${total}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Churn Rate</div>
            <div class="stat-value">${rate}%</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Churned</div>
            <div class="stat-value">${churn}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active</div>
            <div class="stat-value">${active}</div>
        </div>
    `;

    document.getElementById('stats').classList.remove('d-none');
    document.getElementById('dashboard').classList.remove('d-none');

    // === PIE ===
    Plotly.newPlot('pieChart', [{
        labels: ['Churn', 'Active'],
        values: [churn, active],
        hole: 0.6,
        type: 'pie',
        textinfo: 'percent'
    }], {
        margin: { t: 10, b: 10 },
        showlegend: true
    }, { displayModeBar: false });

    // === BAR ===
    const contracts = [...new Set(rawData.map(r => r.Contract))];
    const rates = contracts.map(c =>
        rawData.filter(r => r.Contract === c && r.Churn === 'Yes').length /
        rawData.filter(r => r.Contract === c).length
    );

    Plotly.newPlot('barChart', [{
        x: contracts,
        y: rates,
        type: 'bar',
        marker: { color: '#4c6ef5' }
    }], {
        yaxis: { tickformat: '.0%' },
        margin: { t: 20 }
    }, { displayModeBar: false });
}
</script>
@endpush
