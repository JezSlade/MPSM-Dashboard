
# How This App Talks to MPS Monitor — A Beginner's Guide for Experts

This document explains how a web application interacts with the MPS Monitor API using plain, clear language. It’s based on an in-depth review of the full codebase and its behavior.

---

## 🔐 Step 1: Getting a Token (Logging In)

To talk to MPS Monitor, the app needs a "token"—a kind of digital key. Here's how it gets one:

1. It reads a secret username and password from a hidden file.
2. It sends that info to MPS Monitor’s token endpoint: `/Token`.
3. It also sends something called a client ID and client secret (think of them like another layer of login).
4. If everything checks out, MPS Monitor replies with:
   - An `access_token` (the key)
   - A `refresh_token` (a backup key)
   - An expiration time (like 1 hour)

This token is stored temporarily (in memory or Redis) and used for all future requests.

---

## 🔄 Step 2: Refreshing a Token

When the token gets old, the app uses the `refresh_token` to get a new one—without making the user log in again. This happens automatically in the background.

---

## 🧭 Step 3: Making an API Request

Once the app has a token, it can start asking for information. Here's the process:

1. It chooses a specific MPS Monitor API endpoint (like “show me all printers”).
2. It prepares a little package (called a payload) of any required filters or inputs.
3. It sets a header called `Authorization` with the token.
4. It sends the request over the internet.
5. It gets back a list of data, usually in JSON format (like a big list of facts).

The app can ask for things like:
- All the customers
- A list of printers
- Alerts or events about a device
- Printer supply levels
- Device counters and readings

---

## 🧰 How This Is Organized in the App

- There’s a shared “bootstrap” file that handles all the prep work (reading tokens, sending requests, etc.).
- All API requests go through this system so they all behave the same way.
- Some data is cached—this means saved temporarily—so that if two parts of the app ask the same question, it doesn’t hit MPS Monitor twice.

---

## 📇 How Customers Are Displayed

To show a list of customers:
1. The app sends a token-protected request to the customer list endpoint.
2. It gets back a JSON list of customers.
3. This list is put into a dropdown on the screen.
4. When a user picks a customer, the app saves that choice for later (locally).

---

## 🖨️ How Printers and Devices Are Shown

1. The app asks for all devices for the selected customer.
2. It can also request detailed info: counters, errors, readings, SNMP events.
3. These are shown in cards or widgets.
4. Users can click to drill down into more device-specific data.

---

## ⚙️ Environment Settings That Power Everything

The app relies on a hidden `.env` file that stores:
- The API URL
- Login credentials
- Client info
- Scope definitions

This keeps sensitive information out of the code.

---

## 🧠 Summary

This app is a translator. It reads user actions, turns them into structured API calls, fetches data from MPS Monitor, and displays it neatly. It always ensures:

- Tokens are fresh
- Errors are logged
- Requests are efficient
- Data is safely displayed

It doesn’t expose anything sensitive in public. It’s smart, modular, and well-organized.

---

