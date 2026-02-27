(function () {
  const input = document.currentScript.getAttribute("input");
  const btn = document.currentScript.getAttribute("btn");
  window.addEventListener("load", () => {
    const params = new URLSearchParams(window.location.search);
    if (!params.get("p")) {
      return;
    }
    document.querySelector(input).value = params.get("p");
    document.querySelector(btn).click();
  });
})();
