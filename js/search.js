function myFunction() {
  var input, filter, table, list, a, i, txtValue;
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("myTable");
  list = document.getElementsByClassName("list");
  for (i = 0; i < list.length; i++) {
    a = list[i].getElementsByTagName("a")[0];
    if (a) {
      txtValue = a.textContent || a.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        list[i].style.display = "";
      } else {
        list[i].style.display = "none";
      }
    }       
  }
}