// change EN --> TH header navbar
const btn = document.getElementById('btn');

// ✅ Toggle button text on click
btn.addEventListener('click', function handleClick() {
  const initialText = 'EN';

  if (btn.textContent.toLowerCase().includes(initialText.toLowerCase())) {
    btn.textContent = 'TH';
  } else {
    btn.textContent = initialText;
  }
});

// change EN --> TH side navbar

const btns = document.getElementById('btns');

// ✅ Toggle button text on click
btns.addEventListener('click', function handleClick() {
  const initialText = 'EN';

  if (btns.textContent.toLowerCase().includes(initialText.toLowerCase())) {
    btns.textContent = 'TH';
  } else {
    btns.textContent = initialText;
  }
});

//active button menu
// Add active class to the current button (highlight it)
var header = document.getElementById("navac");
var btnac = header.getElementsByClassName("btnac");
for (var i = 0; i < btnac.length; i++) {
  btnac[i].addEventListener("click", function() {
  var current = document.getElementsByClassName("active");
  if (current.length > 0) { 
    current[0].className = current[0].className.replace(" active", "");
  }
  this.className += " active";
  });
}





