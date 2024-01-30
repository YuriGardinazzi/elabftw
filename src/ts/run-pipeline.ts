



console.log("file running");


const button = document.getElementById('btn-pipeline');



// Token local jenkins:114201cc1b6073abbab8e76fb0bc104d8e
// Function to make the API call
async function postData(url: string, data: Record<string, any>): Promise<any> {
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        // You can add other headers if needed
      },
      body: JSON.stringify(data),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    // Assuming the server sends back JSON data
    const responseData = await response.json();
    return responseData;
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}


button?.addEventListener('click', function handleClick(event) {


  console.log('button clicked');
  const randomColor = `#${Math.floor(Math.random()*16777215).toString(16)}`;
  console.log('calling api');
  const apiUrl = 'https://localhost:3148/call-api.php';
  const requestData = { key1: 'value1', key2: 'value2' };

  postData(apiUrl, requestData)
    .then((response) => {
      console.log('Server response:', response);
    })
    .catch((error) => {
      console.error('Error:', error);
    });
  
  console.log('finished calling API');
  // Change the button color
  button.style.backgroundColor = randomColor;
  console.log(event);
  console.log(event.target);
});


