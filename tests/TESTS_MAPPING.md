# Tests Mapping - User Flow Order
## PHASE 1 - RED

Total: **72 tests** organized by user journey

---

## USER REGISTRATION & VALIDATION (Tests 1-12)

**UserRegistrationTest.php** (3 tests)
- Test 1: A user can be registered with valid credentials
- Test 2: A registered user has a unique ID
- Test 3: Password is not stored in plain text

**UserRegistrationValidationTest.php** (9 tests)
- Test 4: First name cannot be empty
- Test 5: Last name cannot be empty
- Test 6: Email must have a valid format (RFC) - with DataProvider
- Test 7: Email must be unique (no duplicates)
- Test 8: Password must meet minimum length requirement (8 chars)
- Test 9: Password must contain at least one uppercase letter
- Test 10: Password must contain at least one lowercase letter
- Test 11: Password must contain at least one number
- Test 12: Password must contain at least one special character

---

## PASSWORD SECURITY (Tests 13-19)

**PasswordSecurityTest.php** (7 tests)
- Test 13: Password is hashed using Argon2id algorithm
- Test 14: Hashed password can be verified correctly
- Test 15: Wrong password verification fails
- Test 16: Hashed password is encrypted with AES-256
- Test 17: Encrypted hash can be decrypted back to original hash
- Test 18: User password goes through complete security pipeline
- Test 19: Each password hash is unique (salted)

---

## JWT GENERATION & VERIFICATION (Tests 20-28)

**JWTGenerationTest.php** (9 tests)
- Test 20: A JWT token can be generated for a user
- Test 21: JWT token has three parts (header.payload.signature)
- Test 22: JWT payload contains user ID
- Test 23: JWT payload contains user email
- Test 24: JWT payload contains expiration timestamp
- Test 25: JWT token can be verified as valid
- Test 26: Tampered JWT token is rejected
- Test 27: Expired JWT token is rejected
- Test 28: JWT token default expiration is 1 hour

---

## AUTHENTICATION & LOGIN (Tests 29-37)

**UserAuthenticationTest.php** (9 tests)
- Test 29: User can authenticate with correct email and password
- Test 30: Authentication fails with incorrect email
- Test 31: Authentication fails with incorrect password
- Test 32: Authentication verifies password hash correctly
- Test 33: Authentication is case-insensitive for email
- Test 34: Authentication returns user information on success
- Test 35: Multiple failed authentication attempts are tracked
- Test 36: Account is locked after too many failed attempts (5 attempts)
- Test 37: Successful login resets failed attempt counter

---

## PARENT ACCOUNT CREATION (Tests 38-39)

**ParentAccountCreationTest.php** (2 tests)
- Test 38: A parent can be created with a name
- Test 39: A parent cannot be created with an empty name

---

## TEENAGER ACCOUNT CREATION & MANAGEMENT (Tests 40-45)

**ParentAccountManagementTest.php** (3 tests)
- Test 40: A parent can create an account for a teenager
- Test 41: A parent cannot create an account with an empty teenager name
- Test 42: A parent can create and manage multiple teenager accounts

**TeenagerAccountCreationTest.php** (3 tests)
- Test 43: A teenager account can be created with a name
- Test 44: A new account has an initial balance of 0.00€
- Test 45: An account cannot be created with an empty name

---

## DEPOSIT OPERATIONS (Tests 46-51)

**ParentAccountDepositTest.php** (4 tests)
- Test 46: A parent can deposit money into an account
- Test 47: A deposit cannot have a negative or zero amount
- Test 48: A deposit cannot have a negative amount
- Test 49: Cannot deposit on account from different parent (SECURITY)

**TeenagerAccountValidationTest.php** (2 tests - deposit validation)
- Test 50: Cannot deposit a negative amount
- Test 51: Cannot deposit zero amount

---

## EXPENSE OPERATIONS (Tests 52-61)

**ParentAccountExpenseTest.php** (2 tests)
- Test 52: A parent can record an expense
- Test 53: An expense cannot create a negative balance (strict blocking)

**ParentAccountExpenseValidationTest.php** (4 tests)
- Test 54: Cannot record expense with negative amount
- Test 55: Cannot record expense with zero amount
- Test 56: Cannot record expense with empty description
- Test 57: Cannot record expense on account from different parent (SECURITY)

**TeenagerAccountValidationTest.php** (4 tests - expense validation)
- Test 58: Cannot spend a negative amount
- Test 59: Cannot spend zero amount
- Test 60: Cannot spend more than current balance
- Test 61: Cannot spend with empty description

---

## WEEKLY ALLOWANCE (Tests 62-69)

**ParentAccountAllowanceTest.php** (4 tests)
- Test 62: A parent can configure a weekly allowance
- Test 63: A weekly allowance must be strictly positive
- Test 64: Weekly allowance cannot be zero
- Test 65: Cannot set allowance on account from different parent (SECURITY)

**TeenagerAccountValidationTest.php** (4 tests - allowance validation)
- Test 66: Cannot set negative weekly allowance
- Test 67: Cannot set zero weekly allowance
- Test 68: New account has no weekly allowance by default
- Test 69: Cannot apply weekly allowance if not configured

---

## TRANSACTION HISTORY (Tests 70-72)

**TeenagerAccountHistoryTest.php** (3 tests)
- Test 70: Transaction history records deposits
- Test 71: Transaction history records expenses with description
- Test 72: Transaction history records weekly allowances

---

## Summary

| Phase | Domain | Tests | Files |
|-------|--------|-------|-------|
| 1 | User Registration & Validation | 12 | 2 |
| 2 | Password Security | 7 | 1 |
| 3 | JWT Generation | 9 | 1 |
| 4 | Authentication | 9 | 1 |
| 5 | Parent Creation | 2 | 1 |
| 6 | Teenager Creation & Management | 6 | 2 |
| 7 | Deposit Operations | 6 | 2 |
| 8 | Expense Operations | 10 | 3 |
| 9 | Weekly Allowance | 8 | 2 |
| 10 | Transaction History | 3 | 1 |
| **TOTAL** | **10 Phases** | **72 tests** | **14 files** |

---

## User Flow

```
1. User registers → 2. Login/Auth → 3. Create Parent account
                                                ↓
4. Create Teenager accounts → 5. Deposit money → 6. Record expenses
                                                ↓
                        7. Configure allowance → 8. View history
```
