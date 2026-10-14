// Do not allow a negative number in quantity fields.
var inputs = document.querySelectorAll("input[type='number']");

for (var i = 0; i < inputs.length; i++) {
    inputs[i].onchange = function () {
        if (this.value < 0) {
            this.value = 0;
        }
    };
}
