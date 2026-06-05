</div>
</div>
<script>

function openModal(id){ 
    document.getElementById(id).classList.add('open') }

function closeModal(id){ 
    document.getElementById(id).classList.remove('open') }

document.querySelectorAll('.overlay').forEach(o=>{
    o.addEventListener('click',e=>{ 
        if(e.target===o) o.classList.remove('open') })
});

function closeConfirm(){ 
    document.querySelectorAll('.confirm-overlay').forEach(o=>o.classList.remove('open')) }

document.querySelectorAll('.confirm-overlay').forEach(o=>{
    o.addEventListener('click',e=>{ 
        if(e.target===o) o.classList.remove('open') })
});

(function(){
    const p = new URLSearchParams(location.search);
    const s = p.get('success'), e = p.get('error');
    if(s||e) showToast(decodeURIComponent(s||e), s?'success':'error');
    const url=new URL(location); url.searchParams.delete('success'); url.searchParams.delete('error');
    history.replaceState(null,'',url);
})();
function showToast(msg,type='success'){
    const t=document.createElement('div');
    t.className='toast '+type;
    t.innerHTML=(type==='success'?'✅':'❌')+' '+msg;
    document.body.appendChild(t);
    setTimeout(()=>{ t.style.opacity='0'; t.style.transform='translateX(30px)'; t.style.transition='.3s'; setTimeout(()=>t.remove(),300) },3400);
}

function liveSearch(inputId, tableId){
    const q = document.getElementById(inputId).value.toLowerCase();
    document.querySelectorAll('#'+tableId+' tbody tr').forEach(r=>{
        r.style.display = r.textContent.toLowerCase().includes(q)?'':'none';
    });
}
</script>
</body>
</html>