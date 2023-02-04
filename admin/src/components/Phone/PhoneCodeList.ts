const phoneCodes: any = {
  TR: {
    label: 'Türkiye',
    mask: '+90 (###) ### ## ##',
    length: [10, 10],
    phoneCode: '90',
    phoneCountry: 'TR',
  },
  DE: {
    label: 'Germany',
    mask: '+49 (###) #### ###',
    length: [6, 13],
    phoneCode: '49',
    phoneCountry: 'DE',
  },
  US: {
    label: 'United States',
    mask: '+1 (###) ### ## ##',
    length: [10, 10],
    phoneCode: '1',
    phoneCountry: 'US',
  },
};

const extractPhone = (phoneNumber: string, phoneCountry: string) => {
  return {
    phoneNumber:
      phoneCodes[phoneCountry].phoneCode ===
      String(phoneNumber).substring(
        0,
        phoneCodes[phoneCountry].phoneCode.length
      )
        ? String(phoneNumber).substring(
            phoneCodes[phoneCountry].phoneCode.length
          )
        : phoneNumber,
    phoneCode: phoneCodes[phoneCountry].phoneCode,
    phoneCountry: phoneCodes[phoneCountry].phoneCountry,
  };
};

const isValidPhone = (phoneNumber: string, phoneCountry: string) => {
  return (
    phoneNumber.length >= phoneCodes[phoneCountry].length[0] &&
    phoneNumber.length <= phoneCodes[phoneCountry].length[1]
  );
};

export { phoneCodes, isValidPhone, extractPhone };
