let url = 'http://eridanus/assets/healthData.json?rnd=' + Math.random();

const fetchData = (data) =>
  fetch(url).then((response) => response.json())
      .catch((error) => console.log(error));

export default fetchData;