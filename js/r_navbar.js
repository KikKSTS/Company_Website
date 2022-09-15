const toggleButton = document.getElementsByClassName('toggle-btn')[0]
const navbarLinks = document.getElementsByClassName('rnavbar-links')[0]

  toggleButton.addEventListener('click', () => {
    navbarLinks.classList.toggle('active')
  })

  //active button menu
// Add active class to the current button (highlight it)
var rheader = document.getElementById("r_navactive");
var rbtnac = header.getElementsByClassName("r_btnac");
for (var i = 0; i < rbtnac.length; i++) {
  rbtnac[i].addEventListener("click", function() {
  var current = document.getElementsByClassName("active");
  if (current.length > 0) { 
    current[0].className = current[0].className.replace(" active", "");
  }
  this.className += " active";
  });
}