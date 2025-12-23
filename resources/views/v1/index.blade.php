@extends('layouts.app')

@section('content')
<div class="container">

    <h1>Customer Churn Dashboard</h1>

    <!-- Filters -->
    <div style="margin-bottom:20px;">
        <select id="paymentFilter" onchange="applyFilters()"></select>

        <select id="internetFilter" onchange="applyFilters()"></select>

        <button onclick="resetFilters()">Reset Filters</button>
    </div>

    <!-- STATS -->
    <div id="stats"></div>

    <!-- CHARTS -->
    <div style="display: flex; gap: 20px; width: 100%; margin-top:20px;">
        <div id="pieChart" style="flex: 1; height: 400px;"></div>
        <div id="barChart" style="flex: 1; height: 400px;"></div>
    </div>

    <!-- RISK TABLE -->
    <div id="riskTable" style="margin-top:30px;"></div>
    <!-- RISK FACTOR CHART -->
    <div id="riskChart" style="width:100%; height:350px; margin-top:20px;"></div>

    <!-- TENURE TREND -->
    <div id="tenureTrend" style="width:100%; height:400px; margin-top:30px;"></div>

</div>

@endsection
@push('styles')
<style>
body {
    margin: 0;
    font-family: "Inter", sans-serif;
    background: #f5f7fb;
    color: #2c3e50;
}

.container {
    max-width: 1200px;
    margin: auto;
    padding: 30px;
}

h1 {
    font-size: 32px;
    margin-bottom: 25px;
    font-weight: 700;
}

select, button {
    padding: 12px 15px;
    margin-right: 10px;
    border-radius: 10px;
    font-size: 15px;
}

button {
    background: #4c6ef5;
    color: white;
    border: none;
    font-weight: 600;
}

#stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin: 25px 0;
}

#riskTable {
    background: white;
    padding: 20px;
    border-radius: 14px;
    margin-top: 20px;
}
</style>
@endpush
@push('scripts')

<script src="https://cdn.plot.ly/plotly-2.30.0.min.js"></script>

<script>
/* ===============================
   DATA PREP
================================ */
let rawData = @json($datasets).map(r => ({
    tenure: r.tenure,
    Contract: r.contract,
    PaymentMethod: r.payment_method,
    MonthlyCharges: parseFloat(r.monthly_charges) || 0,
    TotalCharges: parseFloat(r.total_charges) || 0,
    InternetService: r.internet_service,
    Churn: r.churn === "Yes" ? 1 : 0
}));

let filtered = [...rawData];

/* ===============================
   FILTER INIT
================================ */
function initFilters(data) {
    const paymentFilter = document.getElementById("paymentFilter");
    const internetFilter = document.getElementById("internetFilter");

    const payments = ["All", ...new Set(data.map(r => r.PaymentMethod))];
    const internets = ["All", ...new Set(data.map(r => r.InternetService))];

    paymentFilter.innerHTML = payments
        .map(p => `<option value="${p}">${p}</option>`)
        .join("");

    internetFilter.innerHTML = internets
        .map(i => `<option value="${i}">${i}</option>`)
        .join("");

    paymentFilter.value = "All";
    internetFilter.value = "All";
}

/* ===============================
   FILTER APPLY
================================ */
function applyFilters() {
    const paymentFilter = document.getElementById("paymentFilter");
    const internetFilter = document.getElementById("internetFilter");

    filtered = [...rawData];

    const pay = paymentFilter.value;
    const net = internetFilter.value;

    if (pay !== "All") filtered = filtered.filter(r => r.PaymentMethod === pay);
    if (net !== "All") filtered = filtered.filter(r => r.InternetService === net);

    updateDashboard();
}


function resetFilters() {
  document.getElementById("paymentFilter").value = "All";
  document.getElementById("internetFilter").value = "All";
  filtered = rawData;
  updateDashboard();
}


/* ===============================
   AGGREGATES
================================ */
function computeAggregates(data) {
    const total = data.length;
    const churn = data.filter(r => r.Churn === 1).length;
    const churnRate = churn / total;

    const avgMonthly = data
        .filter(r => r.Churn === 1)
        .reduce((s, r) => s + r.MonthlyCharges, 0) / churn || 0;

    const revenue = churn * avgMonthly * 12;

    return { total, churn, churnRate, revenue };
}

/* ===============================
   CONTRACT BAR
================================ */
function computeContractRates(data) {
    const map = {};

    data.forEach(r => {
        if (!map[r.Contract]) map[r.Contract] = { total: 0, churn: 0 };
        map[r.Contract].total++;
        if (r.Churn === 1) map[r.Contract].churn++;
    });

    return Object.keys(map).map(k => ({
        contract: k,
        rate: map[k].churn / map[k].total
    }));
}

/* ===============================
   TOP RISK
================================ */
function computeTop3(data) {
    const features = ["Contract", "InternetService", "PaymentMethod"];
    const scores = [];

    features.forEach(feat => {
        const churn = {}, loyal = {};
        let c1 = 0, c0 = 0;

        data.forEach(r => {
            const v = r[feat];
            if (r.Churn === 1) {
                churn[v] = (churn[v] || 0) + 1;
                c1++;
            } else {
                loyal[v] = (loyal[v] || 0) + 1;
                c0++;
            }
        });

        let max = 0;
        new Set([...Object.keys(churn), ...Object.keys(loyal)]).forEach(k => {
            const d = Math.abs(
                (churn[k] || 0) / c1 -
                (loyal[k] || 0) / c0
            );
            max = Math.max(max, d);
        });

        scores.push({ feature: feat, score: max });
    });

    return scores.sort((a, b) => b.score - a.score).slice(0, 3);
}

/* ===============================
   TENURE TREND
================================ */
function computeTenure(data) {
    const buckets = {};

    data.forEach(r => {
        const b = Math.floor(r.tenure / 12) * 12;
        if (!buckets[b]) buckets[b] = { total: 0, churn: 0 };
        buckets[b].total++;
        if (r.Churn === 1) buckets[b].churn++;
    });

    return Object.keys(buckets).sort((a,b)=>a-b).map(b => ({
        tenure: `${b}-${+b + 11}`,
        rate: buckets[b].churn / buckets[b].total
    }));
}


function renderRiskChart(top3) {
    Plotly.newPlot("riskChart", [{
        x: top3.map(r => r.score),
        y: top3.map(r => r.feature),
        type: "bar",
        orientation: "h",
        marker: {
            color: "#4c6ef5"
        }
    }], {
        title: "Top Risk Factors (Churn Impact)",
        xaxis: {
            title: "Impact Score",
            range: [0, 1]
        },
        margin: {
            l: 120
        }
    });
}



/* ===============================
   RENDER
================================ */
function updateDashboard() {
    const stats = document.getElementById("stats");
    // const riskTable = document.getElementById("riskTable");

    const agg = computeAggregates(filtered);

    // =====================
    // STATS
    // =====================
    stats.innerHTML = `
        <div class="stat-card"><b>Total Customers</b><br>${agg.total}</div>
        <div class="stat-card"><b>Churn Rate</b><br>${(agg.churnRate * 100).toFixed(2)}%</div>
        <div class="stat-card"><b>Lost Customers</b><br>${agg.churn}</div>
        <div class="stat-card"><b>Revenue at Risk</b><br>$${Math.round(agg.revenue).toLocaleString()}</div>
    `;

    // =====================
    // PIE CHART
    // =====================
    Plotly.newPlot("pieChart", [{
        labels: ["Churned", "Active"],
        values: [agg.churn, agg.total - agg.churn],
        hole: 0.6,
        type: "pie"
    }]);

    // =====================
    // BAR CHART
    // =====================
    const contracts = computeContractRates(filtered);
    Plotly.newPlot("barChart", [{
        x: contracts.map(c => c.contract),
        y: contracts.map(c => c.rate),
        type: "bar"
    }]);

    // =====================
    // TOP RISK TABLE
    // =====================
   const top3 = computeTop3(filtered);

// // TABLE
// riskTable.innerHTML = `
//     <h3>Top Risk Factors</h3>
//     <table>
//         <tr><th>#</th><th>Feature</th><th>Score</th></tr>
//         ${top3.map((r, i) => `
//             <tr>
//                 <td>${i + 1}</td>
//                 <td>${r.feature}</td>
//                 <td>${r.score.toFixed(3)}</td>
//             </tr>
//         `).join("")}
//     </table>
// `;

// CHART
renderRiskChart(top3);


    // =====================
    // TENURE TREND
    // =====================
    const tenure = computeTenure(filtered);
    Plotly.newPlot("tenureTrend", [{
        x: tenure.map(t => t.tenure),
        y: tenure.map(t => t.rate),
        mode: "lines+markers",
        type: "scatter"
    }]);
}


/* ===============================
   INIT
================================ */
initFilters(rawData);
updateDashboard();
</script>
@endpush