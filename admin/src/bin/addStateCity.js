const axios = require('axios');
const fs = require('fs');
const path = require('path');

const ccList = process.argv.slice(2);
if (ccList.length <= 0) {
  console.log('Ülke kodlarını arada boşlık bırakarak girin!');
  return;
}

const pluckCity = (arr, key, value, valMap) =>
  arr.reduce((acc, obj) => {
    acc[obj[key]] = obj[value].map(valMap);
    return acc;
  }, {});

// Download
const sPath = path.join(__dirname, '../components/Localization/data');
axios
  .get('https://github.com/dr5hn/countries-states-cities-database/raw/master/countries+states+cities.json')
  .then((r) => {
    // Save All Country
    fs.writeFileSync(
      `${sPath}/countries.json`,
      JSON.stringify(
        r.data
          .map(({ name, iso2, phone_code, emoji }) => ({ name, iso2, phone_code, emoji }))
          .sort((a, b) => a.phone_code - b.phone_code),
      ),
    );

    let selCountry = r.data.filter((d) => ccList.includes(d.iso2));

    // Save State
    selCountry.forEach((c) => {
      let states = c.states.map(({ name, state_code }) => ({ name, code: state_code })).sort((a, b) => a.code - b.code);
      fs.writeFileSync(`${sPath}/state/${c.iso2}.json`, JSON.stringify(states));
    });

    // Save City
    selCountry.forEach((c) => {
      let cities = pluckCity(c.states, 'state_code', 'cities', ({ name }) => name);
      fs.writeFileSync(`${sPath}/city/${c.iso2}.json`, JSON.stringify(cities));
    });
  })
  .catch((error) => {
    console.error('Dosya indirilemedi:', error);
  });
