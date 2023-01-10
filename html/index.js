function checkPasswordEquality() {
    // uses built-in form validation error message
    const passwordEl = document.querySelector('#password');
    const confirmEl = document.querySelector('#confirm_password');
    if (confirmEl.value === passwordEl.value) {
        confirmEl.setCustomValidity('');
    } else {
        confirmEl.setCustomValidity('Passwords do not match');
    }
}

const passwordEl = document.querySelector('#password');
const confirmEl = document.querySelector('#confirm_password');
passwordEl.addEventListener("change", checkPasswordEquality);
confirmEl.addEventListener("change", checkPasswordEquality);
