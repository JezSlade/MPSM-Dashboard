<!-- /components/drilldown-modal.php -->
<div id="drilldown-modal" class="modal hidden">
  <div class="modal-content">
    <button class="modal-close" onclick="hideDrilldown()">×</button>
    <h3>Device Detail</h3>
    <pre id="drilldown-content">Loading...</pre>
  </div>
</div>

<style>
.modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}
.modal.hidden {
  display: none;
}
.modal-content {
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
  border-radius: 1rem;
  padding: 1.5rem;
  max-width: 700px;
  width: 90%;
  color: #fff;
  border: 1px solid rgba(255,255,255,0.2);
}
.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  color: white;
  cursor: pointer;
}
</style>

<script>
function hideDrilldown() {
  document.getElementById('drilldown-modal').classList.add('hidden');
}

document.addEventListener('click', function (e) {
  if (e.target.closest('.drilldown-btn')) {
    const id = e.target.closest('.drilldown-btn').dataset.deviceId;
    fetch(`/api/get_device_detail.php?id=${id}`)
      .then(res => res.json())
      .then(data => {
        const cleaned = Object.entries(data.Result || {})
          .filter(([_, v]) => v && v !== "0" && v !== "DEFAULT" && v !== "[]" && v !== "")
          .map(([k, v]) => `${k}: ${v}`)
          .join("\\n");

        document.getElementById('drilldown-content').textContent = cleaned || "No details available.";
        document.getElementById('drilldown-modal').classList.remove('hidden');
      })
      .catch(() => {
        document.getElementById('drilldown-content').textContent = "Error loading device details.";
        document.getElementById('drilldown-modal').classList.remove('hidden');
      });
  }
});
</script>
