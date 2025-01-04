#!/bin/bash

# Pokretanje migracija
echo "Pokrećem migracije..."
php Backend/migrations/migrate.php
if [ $? -ne 0 ]; then
  echo "Greška prilikom pokretanja migracija. Zaustavljam skriptu."
  exit 1
fi

# Pokretanje PHP backend servera
echo "Pokrećem backend server na http://localhost:8000..."
php -S localhost:8000 -t Backend > backend.log 2>&1 &
BACKEND_PID=$!
sleep 2 # Dajte serveru vremena da se pokrene
if ! kill -0 $BACKEND_PID > /dev/null 2>&1; then
  echo "Greška prilikom pokretanja backend servera. Provjerite backend.log za detalje."
  exit 1
fi
echo "Backend server uspješno pokrenut. PID: $BACKEND_PID"

# Pokretanje Angular frontend servera
echo "Pokrećem Angular frontend na http://localhost:4200..."
cd Frontend
ng serve --open > frontend.log 2>&1
if [ $? -ne 0 ]; then
  echo "Greška prilikom pokretanja frontend servera. Provjerite frontend.log za detalje."
  exit 1
fi
