function show(section) {
    document.getElementById("dashboard").style.display = "none";
    document.getElementById("complaints").style.display = "none";

    document.getElementById(section).style.display = "block";
}
