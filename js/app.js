document.querySelectorAll(".sidebar li").forEach(item => {
  item.addEventListener("click", () => {
    const module = item.getAttribute("data-module");
    loadModule(module);
  });
});

function loadModule(name) {
  import(`./modules/${name}.js`)
    .then(module => module.init(document.getElementById("moduleContent")))
    .catch(err => {
      document.getElementById("moduleContent").innerText = `Module failed: ${err}`;
    });
}
