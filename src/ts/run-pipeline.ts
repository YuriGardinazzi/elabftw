

console.log("file running");
const button = document.getElementById('btn-pipeline');

button?.addEventListener('click', function handleClick(event) {
  console.log('button clicked');
  const randomColor = `#${Math.floor(Math.random()*16777215).toString(16)}`;

  // Change the button color
  button.style.backgroundColor = randomColor;
  console.log(event);
  console.log(event.target);
});