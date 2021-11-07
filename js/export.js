document.addEventListener("DOMContentLoaded", function () {
  // Expose to window namespase for testing purposes
  window.zoomTiger = svgPanZoom("#demo-tiger", {
    zoomEnabled: true,
    controlIconsEnabled: true,
    fit: true,
    center: true,
    // viewportSelector: document.getElementById('demo-tiger').querySelector('#g4') // this option will make library to misbehave. Viewport should have no transform attribute
  });

  document.getElementById("enable").addEventListener("click", function () {
    window.zoomTiger.enableControlIcons();
  });

  document.getElementById("disable").addEventListener("click", function () {
    window.zoomTiger.disableControlIcons();
  });

  document.getElementById("menu-toggle").addEventListener("click", function () {
    const menuClassList = document.querySelector(".export__menu").classList;
    const openClass = "export__menu--open";

    menuClassList.contains(openClass)
      ? menuClassList.remove(openClass)
      : menuClassList.add(openClass);
  });
});
