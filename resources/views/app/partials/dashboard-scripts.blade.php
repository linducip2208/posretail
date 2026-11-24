<script>
(function(){
  const fmtRp = v => 'Rp ' + Number(v||0).toLocaleString('id-ID');
  const labels = @json($labels);
  const cur = @json($thisPeriod);
  const prev = @json($prevPeriod);
  const hours = @json($hourLabels);
  const hourData = @json($hourData);
  const outletNames = @json($outletNames);
  const outletTotals = @json($outletTotals);
  if (window.Chart) {
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    const c1 = document.getElementById('dashRevenue');
    if (c1) new Chart(c1, {type:'line', data:{labels, datasets:[
      {label:'Periode ini', data:cur, borderColor:'#0d6efd', backgroundColor:'rgba(13,110,253,.12)', fill:true, tension:.4, cubicInterpolationMode:'monotone', pointRadius:0},
      {label:'Periode lalu', data:prev, borderColor:'#94A3B8', borderDash:[5,5], fill:false, tension:.4, cubicInterpolationMode:'monotone', pointRadius:0}]},
      options:{plugins:{legend:{display:false}, tooltip:{callbacks:{label:c=> ' '+c.dataset.label+': '+fmtRp(c.parsed.y)}}}, scales:{y:{ticks:{callback:v=> (v>=1e6? (v/1e6)+'jt' : v)}}}}});
    const c2 = document.getElementById('salesByOutlet');
    if (c2) new Chart(c2, {type:'doughnut', data:{labels: outletNames.length? outletNames : ['Belum ada data'], datasets:[{data: outletTotals.length? outletTotals : [1], backgroundColor:['#0d6efd','#06B6D4','#10B981','#F59E0B','#8b5cf6'], borderWidth:0}]}, options:{cutout:'68%', plugins:{legend:{display:false}}}});
    const c3 = document.getElementById('hourlySales');
    if (c3) new Chart(c3, {type:'bar', data:{labels:hours, datasets:[{data:hourData, backgroundColor:'#0d6efd', borderRadius:6}]}, options:{plugins:{legend:{display:false}}, scales:{y:{ticks:{callback:v=> v>=1e6? (v/1e6)+'jt' : (v>=1e3? (v/1e3)+'rb' : v)}}}}});
    // Sparkline: garis lurus (tension 0) agar bezier tidak pernah melingkar jadi "elips".
    const spark = (id, data, color) => { const el=document.getElementById(id); if(!el) return; new Chart(el,{type:'line',data:{labels:data.map((_,i)=>i),datasets:[{data,borderColor:color,fill:false,tension:0,pointRadius:0}]},options:{plugins:{legend:{display:false},tooltip:{enabled:false}},scales:{x:{display:false},y:{display:false}},animation:false}}); };
    spark('sparkRev', @json(array_values($sparkRev ?: [0])), '#fff');
    spark('sparkCount', cur.slice(-10), '#fff');
    spark('sparkAvg', cur.slice(-10).map((v,i)=> v>0? v/Math.max(1,@json($todayCount)):0), '#fff');
    spark('sparkMargin', @json(array_values($marginSpark ?? [0])), '#fff');
  }
  // theme toggle sederhana
  document.querySelectorAll('[data-theme-toggle]').forEach(b=>b.addEventListener('click',()=>{
    const r=document.documentElement; const next=r.getAttribute('data-bs-theme')==='dark'?'light':'dark';
    r.setAttribute('data-bs-theme',next);
    try{localStorage.setItem('posretail.theme',JSON.stringify({mode:next}));}catch(e){}
  }));
})();
</script>