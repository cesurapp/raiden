const phoneCodes: any = {
  90: {label: 'Türkiye', mask: '+90 (###) ### ## ##', length: [10, 10], code: '90', country: 'TR'},
  49: {label: 'Germany', mask: '+49 (###) #### ###', length: [6, 13], code: '49', country: 'DE'},
  1: {label: 'United States', mask: '+1 (###) ### ## ##', length: [10, 10], code: '1', country: 'US'},
}

const extractPhone = (phoneNumber: string) => {
  if (phoneNumber) {
    const code = Object.keys(phoneCodes).reverse().find((code) => code === phoneNumber.substring(0, code.length));
    return code ? {
      code: code,
      phone: phoneNumber.substring(code.length),
      country: String(phoneCodes[code].country)
    } : null
  }

  return null
}

const isValidPhone = (phoneNumber: string, countryCode: string | null) => {
  if (!countryCode) {
    countryCode = String(Object.keys(phoneCodes).reverse().find((code) => code === phoneNumber.substring(0, code.length)));
    if (countryCode === 'undefined' || !countryCode) {
      return Boolean(false);
    }
    phoneNumber = phoneNumber.substring(countryCode.length)
  }

  return phoneNumber.length >= phoneCodes[countryCode].length[0]
    && phoneNumber.length <= phoneCodes[countryCode].length[1]
}

export {phoneCodes, isValidPhone, extractPhone};
