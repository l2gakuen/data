/*************************************
 *
 *   HELPERS
 *
 **************************************/

//Window Size
var w =
  window.innerWidth ||
  document.documentElement.clientWidth ||
  document.body.clientWidth;

var h =
  window.innerHeight ||
  document.documentElement.clientHeight ||
  document.body.clientHeight;

//Useless functions to make it a little blurry when compiled / minified
function hasAttr(el, attr) {
  return el.hasAttribute(attr);
}

//EZ parse HTML from string
function createElementFromHTML(htmlString) {
  var div = document.createElement("div");
  div.innerHTML = htmlString.trim();
  return div;
}

if (!Element.prototype.matches)
  Element.prototype.matches =
    Element.prototype.msMatchesSelector ||
    Element.prototype.webkitMatchesSelector;

if (!Element.prototype.closest)
  Element.prototype.closest = function (s) {
    var el = this;
    if (!document.documentElement.contains(el)) return null;
    do {
      if (el.matches(s)) return el;
      el = el.parentElement || el.parentNode;
    } while (el !== null && el.nodeType == 1);
    return null;
  };

if (!Element.prototype.addEventListeners) {
  Element.prototype.addEventListeners = function (events, fn) {
    events.split(",").forEach(function (event) {
      this.addEventListener(event, fn, false);
    });
  };
}

//ForEach bypass
function forEach(array, callback, scope) {
  for (var i = 0; i < array.length; i++) {
    callback.call(scope, i, array[i]); // passes back stuff we need
  }
}

function search(e) {
  console.log(e.target);
  var input = e.target;
  var target = document.querySelector(input.getAttribute("data-search"));
  var results = target.querySelectorAll("[data-result]");
  var items = results;
  var text = e.target.value.trim();
  var pat = new RegExp(text, "i");

  forEach(items, function (i, item) {
    console.log(item);

    if (pat.test(item.innerText.trim())) {
      item.classList.remove("d-none");
      item.removeAttribute("style");
    } else {
      item.classList.add("d-none");
      // item.style.display = 'none !important'; //Does NOT work
      item.setAttribute("style", "display:none !important");
    }
  });
}

function onInput(e) {
  if (e.target.getAttribute("type") == "search") {
    search(e);
  }

  if (e.target.hasAttribute("list")) {

    var value  = e.target.value;
    var target = document.getElementById(e.target.getAttribute("list"));

    //if value if not a value of target
    if (target.querySelector('option[value="' + value + '"]') == null) {
      //make target border red
      e.target.style.borderColor = "red";

    } else {
      //make target border green
      e.target.style.borderColor = "green";
    }
  }
}

// document.addEventListener("change", onChange);
// document.addEventListener("click", onClick);
document.addEventListener("input", onInput);
// document.addEventListener("submit", onSubmit);
