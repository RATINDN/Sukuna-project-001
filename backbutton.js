
document.addEventListener('DOMContentLoaded', () => {
  const backButton = document.querySelector('.backButton');
  
  if (!backButton) return;

  // If no history is available, hide the back button; otherwise, attach a click event.


    backButton.addEventListener('click', () => {
      history.back();
    });
});

document.addEventListener('DOMContentLoaded', () => {
  const redoButton = document.querySelector('.redoButton');
  
  if (!redoButton) return
  

    redoButton.addEventListener('click', () => {
      history.forward();
    });
  

  // Attach click event listener to navigate forward in the browser history.
 
});


