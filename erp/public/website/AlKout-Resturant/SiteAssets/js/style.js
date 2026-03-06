$(window).scroll(function () {
  if ($(document).scrollTop() > 50) {
    $('header').addClass('scroll')
  } else {
    $('header').removeClass('scroll')
  }
})
/* Open the sidenav */
function openNav() {
  document.getElementById("sidenav").style.width = "100%";
}
/* Close/hide the sidenav */
function closeNav() {
  document.getElementById("sidenav").style.width = "0";
}
function openCart() {
  document.getElementById("sideCart").style.width = "450px";
  document.getElementById("blur-overlay").classList.add("active");
  document.body.classList.add("no-scroll");
}

function closeCart() {
  document.getElementById("sideCart").style.width = "0";
  document.getElementById("blur-overlay").classList.remove("active");
  document.body.classList.remove("no-scroll");
}

$(document).ready(function () {
  $('.owl-slider').owlCarousel({
    items: 1,
    loop: true,
    dots: true,
    nav: true,
    autoplay: true,
    smartSpeed: 450,
    rtl: true,
  });

  $('.categories-slider').owlCarousel({
    items: 5,
    loop: false,
    dots: true,
    nav: true,
    margin: 25,
    pagination: false,
    autoplay: false,
    autoplaySpeed: 1000,
    autoplayTimeout: 3000,
    rtl: true,
    responsive: {
      0: {
        items: 1,
        nav: true,
        dots: false
      },

      600: {
        items: 2,
        nav: true
      },

      900: {
        items: 3,
        nav: true
      },

      1200: {
        items: 5,
        nav: true
      }
    }
  });
  $('.offers-slider').owlCarousel({
    items: 3,
    loop: true,
    dots: true,
    nav: true,
    margin: 25,
    pagination: false,
    autoplay: true,
    autoplaySpeed: 1000,
    autoplayTimeout: 3000,
    rtl: true,
    responsive: {
      0: {
        items: 1,
        nav: true,
        dots: false
      },

      600: {
        items: 2,
        nav: true
      },

      900: {
        items: 3,
        nav: true
      },

      // 1200: {
      //   items: 5,
      //   nav: true
      // }
    }
  });

  $('.plates-slider').owlCarousel({
    items: 4,
    loop: false,
    dots: true,
    nav: true,
    margin: 15,
    pagination: false,
    autoplay: false,
    autoplaySpeed: 1000,
    autoplayTimeout: 3000,
    rtl: true,
    responsive: {
      0: {
        items: 1,
        nav: true,
        dots: false
      },

      600: {
        items: 2,
        nav: true
      },

      900: {
        items: 3,
        nav: true
      },

      1200: {
        items: 4,
        nav: true
      }
    }
  });
  $(".owl-prev > span").html('<i class="fas fa-arrow-right"></i>');
  $(".owl-next > span").html('<i class="fas fa-arrow-left"></i>');
  $("#modal").modal("show");


});

$(document).on('show.bs.modal', function (e) {
  var modal = $(e.target).find('.modal-dialog');
  modal.css({
    transform: 'translateY(-50px)',
    opacity: 0
  });
  setTimeout(() => {
    modal.css({
      transform: 'translateY(0)',
      opacity: 1,
      transition: 'all 0.5s ease-out'
    });
  }, 10);
});

$(document).on('hide.bs.modal', function (e) {
  var modal = $(e.target).find('.modal-dialog');
  modal.css({
    transform: 'translateY(-50px)',
    opacity: 0,
    transition: 'all 0.5s ease-in'
  });
});


// otp password time counter
function moveToNext(current, nextId) {
  if (current.value.length === 1) {
    const nextInput = document.getElementById(nextId);
    if (nextInput) nextInput.focus();
  }
}

function moveToPrevious(event, prevId) {
  if (event.key === 'Backspace' && event.target.value === '') {
    const prevInput = document.getElementById(prevId);
    if (prevInput) prevInput.focus();
  }
}
function startCountdown(durationInSeconds) {
  const timerElement = document.getElementById('timer');
  let remainingTime = durationInSeconds;

  timerElement.textContent = `  الوقت المتبقي ${remainingTime} `;

  const countdown = setInterval(() => {
    remainingTime -= 1;
    timerElement.textContent = `  الوقت المتبقي ${remainingTime} `;

    if (remainingTime <= 0) {
      clearInterval(countdown);
      timerElement.textContent = 'انتهي الوقت ';
      disableInputs();
    }
  }, 1000); // Decrements every 1 second
}

// Disable all input fields when time is up
function disableInputs() {
  document.querySelectorAll('.code-input input').forEach(input => {
    input.disabled = true;
  });
}

// Start the countdown timer with 1:30 on page load
window.onload = function () {
  startCountdown(90); // 90 seconds for 1:30
};

// Function to start the countdown
// function startCountdown(seconds) {
//   var timerElement = document.getElementById("timer"); // Assume you have a <p> with id="timer"
//   var interval = setInterval(function () {
//     // Calculate minutes and seconds
//     var minutes = Math.floor(seconds / 60);
//     var remainingSeconds = seconds % 60;

//     // Format minutes and seconds to always show two digits
//     timerElement.innerHTML = formatTime(minutes) + ":" + formatTime(remainingSeconds);
//     seconds--;
//     if (seconds < 0) {
//       clearInterval(interval);
//       timerElement.innerHTML = "00:00";
//     }
//   }, 1000);
// }

function formatTime(time) {
  return time < 10 ? "0" + time : time;
}

// logout modal
//   document.addEventListener("DOMContentLoaded", function () {
//     const logoutBtn = document.querySelector("#profileModal .fa-sign-out-alt").closest("a");

//     if (logoutBtn) {
//       logoutBtn.addEventListener("click", function (event) {
//         event.preventDefault();

//         const profileModal = document.getElementById("profileModal");
//         const logoutModal = new bootstrap.Modal(document.getElementById("logoutModal"));

//         if (profileModal) {
//           const profileInstance = bootstrap.Modal.getInstance(profileModal);
//           if (profileInstance) {
//             profileInstance.hide();
//           }
//         }
//         logoutModal.show();
//       });
//     }
//   });


// notfications-alert modal
document.addEventListener("DOMContentLoaded", function () {
  const notificationSwitch = document.getElementById("notificationSwitch");
  const profileBody = document.querySelector(".profile-body");
  const notificationBody = document.querySelector(".notification-body");
  const cancelNotification = document.getElementById("cancelNotification");

  // Handle switch toggle
  notificationSwitch.addEventListener("change", function () {
    if (this.checked) {
      profileBody.classList.add("d-none"); // Hide profile body
      notificationBody.classList.remove("d-none"); // Show notification body
    } else {
      profileBody.classList.remove("d-none"); // Show profile body
      notificationBody.classList.add("d-none"); // Hide notification body
    }
  });

  // Handle cancel button in notification body
  cancelNotification.addEventListener("click", function () {
    profileBody.classList.remove("d-none"); // Show profile body
    notificationBody.classList.add("d-none"); // Hide notification body
    notificationSwitch.checked = false; // Reset switch
  });
});

// wizard js

$(document).ready(function () {
  //Enable Tooltips
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  //Advance Tabs
  $(".next").click(function () {
    const nextTabLinkEl = $(".nav-tabs .active")
      .closest("li")
      .next("li")
      .find("a")[0];
    const nextTab = new bootstrap.Tab(nextTabLinkEl);
    nextTab.show();
  });

  $(".previous").click(function () {
    const prevTabLinkEl = $(".nav-tabs .active")
      .closest("li")
      .prev("li")
      .find("a")[0];
    const prevTab = new bootstrap.Tab(prevTabLinkEl);
    prevTab.show();
  });
});


// *********************************************
//  product quantity
let quantity = 1;
// function increaseQuantity(ele) {
//   quantity++;
//   console.log(ele);
//   var total = $(ele).parent().parent().find('.total-price').val();
//   console.log(total);

//   $(ele).parent().parent().find('.total-price').val(total * quantity);
//   updateQuantityDisplay();
// }
function increaseQuantity(ele) {
  // Get the quantity span and parse its current value
  let quantityElement = $(ele).siblings('.num');
  let quantity = parseInt(quantityElement.text());

  // Increment the quantity
  quantity++;

  // Update the quantity display
  quantityElement.text(quantity);

  // Get the unit price from the data attribute
  let unitPrice = parseFloat($('#total-price').data('unit-price'));

  // Calculate the new total price
  let newTotal = (unitPrice * quantity).toFixed(2);

  // Update the total price in the modal
  $('#total-price').text(newTotal);
  // $('#dish-price').text(newTotalPrice.toFixed(2));
}
function decreaseQuantity(ele) {
  // Get the quantity span and parse its current value
  let quantityElement = $(ele).siblings('.num');
  let quantity = parseInt(quantityElement.text());

  // Decrement the quantity, but ensure it doesn't go below 1
  if (quantity > 1) {
    quantity--;

    // Update the quantity display
    quantityElement.text(quantity);

    // Get the unit price from the data attribute
    let unitPrice = parseFloat($('#total-price').data('unit-price'));

    // Calculate the new total price
    let newTotal = (unitPrice * quantity).toFixed(2);

    // Update the total price in the modal
    $('#total-price').text(newTotal);

  }
}

function updateQuantityDisplay() {
  document.getElementById('quantity').textContent = quantity;
}
// **************************************************
// eye password hide and show
const passwordInput = document.getElementById('passwordInput');
const togglePasswordButton = document.getElementById('togglePassword');
const eyeIcon = document.getElementById('eyeIcon');

togglePasswordButton.addEventListener('click', function () {
  // Toggle the type attribute of the password input
  passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';

  // Toggle the eye icon class
  eyeIcon.classList.toggle('fa-eye');
  eyeIcon.classList.toggle('fa-eye-slash');
});

//  *********

// login and register modal ***************************

document.getElementById('showLoginLink').addEventListener('click', function (e) {
  e.preventDefault();
  document.getElementById('registerBody').classList.add('d-none');
  document.getElementById('loginBody').classList.remove('d-none');
});

document.getElementById('showRegisterLink').addEventListener('click', function (e) {
  e.preventDefault();
  document.getElementById('loginBody').classList.add('d-none');
  document.getElementById('registerBody').classList.remove('d-none');
});
document.getElementById('showforgetLink').addEventListener('click', function (e) {
  e.preventDefault();
  document.getElementById('loginBody').classList.add('d-none');
  document.getElementById('forgetBody').classList.remove('d-none');
});
document.getElementById('sendForgetButton').addEventListener('click', function (e) {
  e.preventDefault();
  document.getElementById('forgetBody').classList.add('d-none');
  document.getElementById('otpBody').classList.remove('d-none');
});
document.getElementById('sendForgetButton').addEventListener('click', function (e) {
  e.preventDefault();
  document.getElementById('forgetBody').classList.add('d-none');
  document.getElementById('otpBody').classList.remove('d-none');
});

document.getElementById('sendOtpButton').addEventListener('click', function (e) {
  e.preventDefault();

  document.getElementById('otpBody').classList.add('d-none');
  document.getElementById('otpDoneBody').classList.remove('d-none');
  setTimeout(function () {
    document.getElementById('otpDoneBody').classList.add('d-none');
    document.getElementById('resetBody').classList.remove('d-none');
  }, 5000);
});



// end login functions **********************

// counter function
document.addEventListener("DOMContentLoaded", () => {
  const sideCartPlates = document.querySelectorAll(".sideCart-plate");

  sideCartPlates.forEach((plate, index) => {
    const plus = plate.querySelector(".inc.plus"),
      minus = plate.querySelector(".dec.minus"),
      num = plate.querySelector(".num");
    // Increment functionality
    plus.addEventListener("click", () => {
      let value = parseInt(num.innerText, 10);
      value++;
      num.innerText = value;
    });
    // Decrement functionality
    minus.addEventListener("click", () => {
      let value = parseInt(num.innerText, 10);
      if (value > 1) {
        value--;
        num.innerText = value;
      }
    });
  });
});

// rate-stars
document.addEventListener('DOMContentLoaded', function () {
  const stars = document.querySelectorAll('.star');
  let currentRating = 0;

  stars.forEach(star => {
    star.addEventListener('click', function () {
      const rating = parseInt(this.getAttribute('data-value'));

      // If the clicked star is already the current rating, reset to 0
      if (currentRating === rating) {
        currentRating = 0;
        resetStars();
      } else {
        currentRating = rating;
        highlightStars(rating);
      }
    });
  });

  // Highlight stars up to the given rating
  function highlightStars(rating) {
    resetStars();
    stars.forEach(star => {
      if (parseInt(star.getAttribute('data-value')) <= rating) {
        star.classList.add('selected');
      }
    });
  }

  // Reset all stars to the default state
  function resetStars() {
    stars.forEach(star => {
      star.classList.remove('selected');
    });
  }
  const ratingInputs = document.querySelectorAll('input[name="rating"]');
  const ratingMessage = document.querySelector('.rating-message');

  // Define messages for each rating
  const ratingMessages = {
    1: "سيئة",
    2: "يمكن ان يكون افضل",
    3: "مرضية",
    4: "ممتعة",
    5: "ممتازة"
  };

  ratingInputs.forEach(input => {
    input.addEventListener('change', function () {
      const rating = parseInt(this.id.split('-')[1]);

      // Update the message based on the rating
      if (rating in ratingMessages) {
        ratingMessage.textContent = ratingMessages[rating];
      } else {
        ratingMessage.textContent = "قييم تجربتك"; // Default message
      }
    });
  });

  // Reset message when no rating is selected
  document.querySelector('.rating').addEventListener('click', function (e) {
    if (e.target.tagName === 'LABEL' && !document.querySelector('input[name="rating"]:checked')) {
      ratingMessage.textContent = "قييم تجربتك";
    }
  });
});



// Close the cart when clicking outside (on the blur overlay)
document.getElementById("blur-overlay").addEventListener("click", closeCart);

function showFirstPhase() {
  document.querySelector('.third-phase').classList.add('d-none');
  document.querySelector('.first-phase').classList.remove('d-none');
}
function showSecondPhase() {
  document.querySelector('.first-phase').classList.add('d-none');
  document.querySelector('.second-phase').classList.remove('d-none');
}

function showThirdPhase() {
  document.querySelector('.second-phase').classList.add('d-none');
  document.querySelector('.third-phase').classList.remove('d-none');
}
// function showAddPhase() {
//   document.querySelector('.fourth-phase').classList.add('d-none');
//   document.querySelector('.add-phase').classList.remove('d-none');
// }

function showAddPhase2() {
  document.querySelector('.fourth-phase').classList.add('d-none');
  document.querySelector('.add-phase2').classList.remove('d-none');
}
function showAddPhase3() {
  document.querySelector('.add-phase2').classList.add('d-none');
  document.querySelector('.add-phase3').classList.remove('d-none');
}


