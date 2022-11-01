let url = 'http://eridanus/assets/healthData.json?rnd=' + Math.random();

const fetchData = (data) =>
  fetch(url, { timeout: 3000 }).then((response) => response.json())
      .catch((error) => console.log(error));

export default fetchData;