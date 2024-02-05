



console.log("file running");


const button = document.getElementById('btn-pipeline');



// Token local jenkins:114201cc1b6073abbab8e76fb0bc104d8e
// Function to make the API call

async function postData(url: string, data: Record<string, any>): Promise<any> {
  console.log("postData:",data, " URL: ",url);
  console.log("postData stringify :", JSON.stringify(data));
  //console.log(JSON.parse(data));

  //-------------
  const requestData = {
    key1: 'value1',
    key2: 'value2',
  };

 // console.log(JSON.parse(JSON.stringify(requestData)));
  //-------------
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        // You can add other headers if needed
      },
      body:JSON.stringify(data)
     // body: '',//JSON.stringify(data)
    });

    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    // Assuming the server sends back JSON data
    const responseData = await response.text();
    return responseData;
  } catch (error) {
    console.error('Error postData:', error);
    throw error;
  }
}

const output_div = document.getElementById('div-output-pipeline');

button?.addEventListener('click', function handleClick(event) {

  output_div.innerText = 'Running pipeline...';
  output_div.style.backgroundColor = 'yellow';
  console.log('button clicked');
  const randomColor = `#${Math.floor(Math.random()*16777215).toString(16)}`;
  console.log('calling api');
  
  const apiUrl = 'https://localhost:3148/call-api.php';
  const requestData = { key1: 'value1', key2: 'value2' };
  console.log("Request data: ", requestData);
  console.log("p2 requestData");
  console.log(requestData);
  postData(apiUrl, requestData)
    .then((response) => {
      const myDictionary: { [key: string]: string } = JSON.parse(response);

      console.log('Risposta del server:', myDictionary.message);
      console.log('Status: ', myDictionary.status);
      output_div.innerText = myDictionary.status;
      output_div.style.backgroundColor = 'green';
    })
    .catch((error) => {
      console.error('Error pippo:', error);
      output_div.innerText = 'Errore';
      output_div.style.backgroundColor = 'red';
    });

  console.log('finished calling API');
  // Change the button color
  //button.style.backgroundColor = randomColor;
  console.log(event);
  console.log(event.target);
});


