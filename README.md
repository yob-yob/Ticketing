# Online Event Reservation System  

A Laravel-based API application for managing events and ticket reservations, designed as part of a backend developer test.  

## Features  

### Authentication  

- User registration and login using **Laravel Sanctum** for API token-based authentication.  

### Event Management  

- **Create Events**: Users can create events with details including:
  - Title  
  - Description  
  - Date and time  
  - Location  
  - Price  
  - Attendee limit  
- **List Events**: View all available events with relevant details.  

### Ticket Reservation  

- Reserve tickets for events if:  
  - The attendee limit hasn’t been reached.  
  - The reservation deadline hasn’t passed.  

### Event Reviews  

- Users who have attended an event can:  
  - Leave a review with a rating (1-5 stars) and a comment.  
  - View all reviews for an event, including its average rating.  

### Tests  

- Automated tests ensure data validation, feature correctness, and API reliability.  

---

## Installation  

1. Clone the repository:  

   ```bash  
   git clone https://github.com/yob-yob/Ticketing.git  
   cd Ticketing  
   ```  

2. Install dependencies:  

   ```bash  
   composer install  
   ```  

3. Set up the environment:  
   - Copy `.env.example` to `.env`:  
  
     ```bash  
     cp .env.example .env  
     ```  

   - Update database and other configuration values in `.env`.  

4. Run database migrations and seeders:  

   ```bash  
   php artisan migrate --seed  
   ```  

5. Generate an API key for Laravel Sanctum:  
  
   ```bash  
   php artisan key:generate  
   ```  

6. Start the development server:  

   ```bash  
   php artisan serve  
   ```  

---

## API Endpoints  

### Authentication

- **Register**: `POST /register`  
- **Login**: `POST /login`  
- **User**: `POST /api/user`  

### Events  

- **Create Event**: `POST /api/event/create`  
- **List Events**: `GET /api/event/index`  

### Reservations  

- **Reserve Ticket**: `POST /api/event/{event}/reserve`  

### Reviews  

- **Add Review**: `POST /api/event/{event}/review`  
- **List Reviews**: `GET /api/event/{event}/review/index`  

---

## Technologies Used  

- **Laravel 11**  
- **Sqlite**  
- **Laravel Sanctum** for authentication  
- **PestPHP** for testing  

---

## Project Assumptions  

1. Reviews can only be added after attending an event, determined programmatically.  
2. Events can be booked until the reservation deadline or attendee limit is reached.  
3. The app follows RESTful API standards for route naming and responses.  

---

## Testing  

Run the test suite using:  

```bash  
php artisan test  
```  

---

## Notes  

This project was completed as part of an evaluation for backend development proficiency. If you have any questions or need clarification, feel free to reach out.  