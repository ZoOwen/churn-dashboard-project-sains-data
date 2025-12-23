@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Dashboard Churn Analysis</h2>
    <p>Pilih dataset CSV untuk dianalisa.</p>

    {{-- Select Dataset --}}
    <div class="mb-3 d-flex align-items-center gap-2">
        <select id="datasetSelect" class="form-select w-auto">
            @foreach($datasets as $ds)
                <option value="{{ $ds->stored_filename }}">{{ $ds->name }}</option>
            @endforeach
        </select>
        <button id="processBtn" class="btn btn-primary">Proses</button>
    </div>

    {{-- Filters --}}
    <div id="filtersContainer" class="mb-3 d-none d-flex align-items-center gap-2">
        <select id="paymentFilter" class="form-select w-auto" onchange="applyFilters()">
            <option value="All">All</option>
        </select>
        <select id="internetFilter" class="form-select w-auto" onchange="applyFilters()">
            <option value="All">All</option>
        </select>
        <button class="btn btn-secondary" onclick="resetFilters()">Reset Filters</button>
    </div>

    {{-- Loading Overlay --}}
    <div id="loadingOverlay" class="loading-overlay hidden">
        <div class="lds-roller">
            <div></div><div></div><div></div><div></div>
            <div></div><div></div><div></div><div></div>
        </div>
        <div class="pulse-text mt-4" id="loadingText">Memproses dataset...</div>
        <div class="progress-container">
            <div class="progress">
                <div class="progress-bar"></div>
            </div>
        </div>
        <div class="text-muted mt-2 small" id="loadingSubtext">Analisis churn sedang berjalan...</div>
    </div>

    {{-- Dashboard Container --}}
    <div id="dashboardContainer" class="d-none">
        {{-- Stats Cards --}}
        <div id="stats" class="row g-3 mb-4"></div>

        {{-- Top Charts --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-box">
                    <h5>Customer Churn Overview</h5>
                    <div id="pieChart" style="height:300px;"></div>
                    <p class="text-muted small">Distribusi pelanggan yang churn vs loyal.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-box">
                    <h5>Churn Rate by Contract</h5>
                    <div id="barChart" style="height:300px;"></div>
                    <p class="text-muted small">Tingkat churn berbeda tiap jenis kontrak.</p>
                </div>
            </div>
        </div>

        {{-- Bottom Charts --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-box">
                    <h5>Top 3 Risk Factors</h5>
                    <div id="riskChart" style="height:300px;"></div>
                    <p class="text-muted small">Faktor paling berpengaruh terhadap churn.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-box">
                    <h5>Tenure Trend</h5>
                    <div id="tenureTrend" style="height:300px;"></div>
                    <p class="text-muted small">Pola churn berdasarkan lama pelanggan bergabung.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Stats Cards */
.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    text-align: center;
}
.stat-value {
    font-size: 28px;
    font-weight: 700;
}
.stat-label {
    font-size: 14px;
    color: #6c757d;
}

/* Chart Box */
.chart-box {
    background:white;
    padding:15px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

/* Loading Overlay */
.loading-overlay.hidden { display:none; }
.loading-overlay {
    position:fixed;
    top:0; left:0; width:100%; height:100%;
    background: rgba(255,255,255,0.8);
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    z-index:9999;
}
.lds-roller { display:inline-block; position:relative; width:80px; height:80px; }
.lds-roller div {
    animation: lds-roller 1.2s cubic-bezier(0.5,0,0.5,1) infinite;
    transform-origin:40px 40px;
}
.lds-roller div:after { content:""; display:block; position:absolute; width:7px; height:7px; border-radius:50%; background:#4c6ef5; margin:-4px 0 0 -4px; top:37px; left:37px; }
.lds-roller div:nth-child(1) { animation-delay:-0.036s; }
.lds-roller div:nth-child(2) { animation-delay:-0.072s; }
.lds-roller div:nth-child(3) { animation-delay:-0.108s; }
.lds-roller div:nth-child(4) { animation-delay:-0.144s; }
.lds-roller div:nth-child(5) { animation-delay:-0.18s; }
.lds-roller div:nth-child(6) { animation-delay:-0.216s; }
.lds-roller div:nth-child(7) { animation-delay:-0.252s; }
.lds-roller div:nth-child(8) { animation-delay:-0.288s; }
@keyframes lds-roller {
    0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); }
}

/* Progress Bar */
.progress { width:200px; height:6px; background:#e9ecef; border-radius:3px; overflow:hidden; margin-top:15px; }
.progress-bar { width:0; height:100%; background:#4c6ef5; animation: progress-animation 3s linear forwards; }
@keyframes progress-animation { from {width:0;} to {width:100%;} }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>
<script src="https://cdn.plot.ly/plotly-2.30.0.min.js"></script>
<script>
let rawData = [], filtered = [];

// --- Clean Data
function cleanData(data) {
    return data.map(r => ({
        ...r,
        TotalCharges: parseFloat(r.TotalCharges) || null,
        Churn: r.Churn === "Yes" ? 1 : 0,
    })).filter(r => r.TotalCharges !== null);
}

// --- Init Filters
function initFilters(data) {
    filtered = rawData = data;
    const paymentSel = document.getElementById("paymentFilter");
    const internetSel = document.getElementById("internetFilter");
    const payments = ["All", ...new Set(data.map(r => r.PaymentMethod))];
    const internets = ["All", ...new Set(data.map(r => r.InternetService))];
    paymentSel.innerHTML = payments.map(p=>`<option>${p}</option>`).join('');
    internetSel.innerHTML = internets.map(i=>`<option>${i}</option>`).join('');
    document.getElementById('filtersContainer').classList.remove('d-none');
}

// --- Apply Filters
function applyFilters() {
    const pay = document.getElementById("paymentFilter").value;
    const net = document.getElementById("internetFilter").value;
    filtered = rawData.filter(r=>(pay==="All"||r.PaymentMethod===pay)&&(net==="All"||r.InternetService===net));
    updateDashboard();
}

// --- Reset Filters
function resetFilters() {
    document.getElementById("paymentFilter").value="All";
    document.getElementById("internetFilter").value="All";
    filtered=[...rawData];
    updateDashboard();
}

// --- Update Dashboard
function updateDashboard() {
    if(!filtered.length) return;

    const total=filtered.length;
    const churn=filtered.filter(r=>r.Churn===1).length;
    const churnRate=churn/total;
    const avgMonthly=filtered.reduce((s,r)=>s+r.MonthlyCharges,0)/total;
    const revenue=churn*avgMonthly*12;

    // --- Stats Cards ---
    const statsContainer = document.getElementById('stats');
    statsContainer.innerHTML = `
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Total Customers</div><div class="stat-value">${total}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Churn Rate</div><div class="stat-value">${(churnRate*100).toFixed(2)}%</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Lost Customers</div><div class="stat-value">${churn}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Revenue at Risk</div><div class="stat-value">$${Math.round(revenue)}</div></div></div>
    `;

    // --- Charts ---
    const contracts = [...new Set(filtered.map(r=>r.Contract))];
    const rates = contracts.map(c=>filtered.filter(r=>r.Contract===c).filter(r=>r.Churn===1).length / filtered.filter(r=>r.Contract===c).length);
    Plotly.newPlot('barChart',[{x:contracts,y:rates,type:'bar'}]);
    Plotly.newPlot('pieChart',[{labels:['Churn','Loyal'],values:[churn,total-churn],type:'pie',hole:0.6}]);

    const categories=["Contract","InternetService","PaymentMethod"];
    let scores=[];
    categories.forEach(feat=>{
        const churnCounts={},noCounts={};let totalChurn=0,totalNo=0;
        filtered.forEach(r=>{
            const v=r[feat];
            if(r.Churn===1){churnCounts[v]=(churnCounts[v]||0)+1;totalChurn++;}
            else{noCounts[v]=(noCounts[v]||0)+1;totalNo++;}
        });
        let maxDiff=0;
        const cats=new Set([...Object.keys(churnCounts),...Object.keys(noCounts)]);
        cats.forEach(cat=>{const c1=(churnCounts[cat]||0)/totalChurn;const c0=(noCounts[cat]||0)/totalNo;maxDiff=Math.max(maxDiff,Math.abs(c1-c0));});
        scores.push({feature:feat,score:maxDiff});
    });
    scores=scores.sort((a,b)=>b.score-a.score).slice(0,3);
    Plotly.newPlot('riskChart',[{x:scores.map(s=>s.feature),y:scores.map(s=>s.score),type:'bar',marker:{color:'#f39c12'}}]);

    const tenureBuckets={};
    filtered.forEach(r=>{
        const t=r.tenure||0;
        const bucket=Math.floor(t/12)*12;
        if(!tenureBuckets[bucket]) tenureBuckets[bucket]={count:0,churn:0};
        tenureBuckets[bucket].count++;if(r.Churn===1) tenureBuckets[bucket].churn++;
    });
    const tenureData=Object.keys(tenureBuckets).sort((a,b)=>a-b).map(b=>({tenureGroup:`${b}-${parseInt(b)+11}`,churnRate:tenureBuckets[b].churn/tenureBuckets[b].count}));
    Plotly.newPlot('tenureTrend',[{x:tenureData.map(t=>t.tenureGroup),y:tenureData.map(t=>t.churnRate),type:'scatter',mode:'lines+markers'}]);
}

// --- Loading Overlay ---
function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    const progressBar = document.querySelector('.progress-bar');
    progressBar.style.animation = 'none';
    void progressBar.offsetWidth;
    progressBar.style.animation = 'progress-animation 3s linear forwards';
    overlay.classList.remove('hidden');

    return setTimeout(()=>{},3000); // just placeholder for interval handle
}
function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

// --- Process Dataset ---
document.getElementById('processBtn').addEventListener('click', function(){
    const file = document.getElementById('datasetSelect').value;
    const url = "/storage/datasets/" + file;

    fetch(url,{method:'HEAD'}).then(res=>{
        if(res.ok){
            const messageInterval = showLoading();
            document.getElementById('dashboardContainer').classList.add('d-none');

            setTimeout(()=>{
                Papa.parse(url,{
                    download:true,
                    header:true,
                    dynamicTyping:true,
                    complete:function(result){
                        hideLoading(messageInterval);
                        rawData=cleanData(result.data);
                        filtered=rawData;
                        initFilters(rawData);
                        document.getElementById('dashboardContainer').classList.remove('d-none');
                        updateDashboard();
                    },
                    error:function(err){
                        hideLoading(messageInterval);
                        console.error(err);
                        alert('Terjadi error saat memproses file.');
                    }
                });
            },3000); // simulasi loading 3 detik
        } else {
            alert('File dataset tidak ditemukan!');
        }
    }).catch(err=>{
        alert('Terjadi error saat mengakses file.');
    });
});
</script>
@endpush
