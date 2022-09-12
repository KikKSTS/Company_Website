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

// ----------------------------------------------------------------
// sidebar
function openNav(){
  document.getElementById("sidenav").style.width = "40%";
}
function closeNav(){
  document.getElementById("sidenav").style.width = "0%";
}

