document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.dataset.confirm))e.preventDefault()}));
const search=document.getElementById('jobSearch'); if(search) search.addEventListener('input',()=>document.querySelectorAll('.job-item').forEach(x=>x.hidden=!x.innerText.toLowerCase().includes(search.value.toLowerCase())));
