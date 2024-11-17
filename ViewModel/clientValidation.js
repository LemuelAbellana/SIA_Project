class ClientValidation {
  static validateContactNumber(contactNumber) {
    return /^\d{11}$/.test(contactNumber);
  }

  static validateNumberOfPeople(number) {
    return !isNaN(number) && number > 0 && number <= 1000;
  }
}
