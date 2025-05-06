#!/bin/zsh

# --- Configuration ---

CURL_CMD="/usr/bin/curl"
SED_CMD="/usr/bin/sed"
JQ_CMD="/usr/bin/jq"

BASE_URL="http://localhost:82/recipes/api"
COOKIE_FILE="api_cookie.txt"
USERNAME="sousou"
BACKUP_DIR="../backup-jsons"
PASSWORD="Souleimane21?"
APACHE_USER="daemon"
APACHE_GROUP="daemon"


PIZZA_ID="f0e9d8c7-b6a5-4321-fedc-ba9876543210"
SUSHI_ID="10293847-56ab-cdef-1234-567890abcdef"
SALAD_ID="98765432-10fe-dcba-0987-654321fedcba"
ESCARGOTS_ID="b1a2c3d4-e5f6-7890-abcd-ef1234567890"
PANCAKE_ID="abcdef12-3456-7890-fedc-ba9876543210"

# --- Helper Function ---


authenticated_request() {
  local method=$1
  local path=$2
  local data=$3
  local use_jq=$4

  local args=(-s -L -i -b "$COOKIE_FILE" -X "$method")

  if [[ -n "$data" ]]; then

    args+=(-H "Content-Type: application/json" -d "$data")
  fi

  echo "\n--- Request: $method $BASE_URL$path ---"

  response=$("$CURL_CMD" "${args[@]}" "$BASE_URL$path")
  echo "Raw Response:"
  echo "$response"



  body=$(echo "$response" | "$SED_CMD" '1,/^\r?$/d')

  if [[ "$use_jq" == "true" ]] && [[ -n "$body" ]]; then
      echo "\nAttempting jq pretty-print:"

      echo "$body" | "$JQ_CMD" .
  elif [[ -n "$body" ]]; then
      echo "\n(jq not used for this request)"
  else
       echo "\n(No response body)"
  fi

  echo "\n--- End Request ---"
}

# --- Script Start ---
echo "Starting API Test Script..."



echo "\n[Step 0] Setting permissions for api/data/... (may require password)"
sudo chmod 777 api/data/recipes.json api/data/users.json

if command -v chown >/dev/null 2>&1; then
    sudo chown -R "$APACHE_USER:$APACHE_GROUP" api/data
else
    echo "Warning: 'chown' command not found, skipping owner change."
fi
echo "Permissions set (check for errors above)."
sleep 1

# 1. Clean up old cookie file if it exists
echo "\n[Step 1] Removing old cookie file..."
rm -f "$COOKIE_FILE"
rm -f "api/$COOKIE_FILE"
echo "Done."

# 2. Login as 'sousou' and save cookie
echo "\n[Step 2] Logging in as user '$USERNAME'..."
LOGIN_RESPONSE=$("$CURL_CMD" -s -L \
  -X POST \
  -H "Content-Type: application/json" \
  -c "$COOKIE_FILE" \
  -d "{\"username\": \"$USERNAME\", \"password\": \"$PASSWORD\"}" \
  "$BASE_URL/auth/login")


if "$JQ_CMD" -e '.status == "success"' <<< "$LOGIN_RESPONSE" > /dev/null; then
  USER_ID=$(echo "$LOGIN_RESPONSE" | "$JQ_CMD" -r '.userId')
  echo "Login successful. User ID: $USER_ID. Cookie saved to $COOKIE_FILE."
  echo "Login Response JSON:"
  echo "$LOGIN_RESPONSE" | "$JQ_CMD" .
else
  echo "Login failed!"
  echo "Raw Login Response: $LOGIN_RESPONSE"
  if [[ -f "$COOKIE_FILE" ]]; then
       echo "Cookie file content on failure:"
       cat "$COOKIE_FILE"
  fi
  exit 1
fi
sleep 1

# --- Authenticated Operations ---

# 3. Get Published Recipes (Use jq here)
echo "\n[Step 3] Getting published recipes..."
"$CURL_CMD" -s -L "$BASE_URL/recipes/published" | "$JQ_CMD" .
echo "\n--- End Request ---"

# 4. Get Specific Recipe (Sushi - Use jq here)
echo "\n[Step 4] Getting specific recipe (Sushi ID: $SUSHI_ID)..."
"$CURL_CMD" -s -L "$BASE_URL/recipes/$SUSHI_ID" | "$JQ_CMD" .
echo "\n--- End Request ---"

# 5. Search Recipes (Use jq here)
echo "\n[Step 5] Searching for recipes containing 'saumon'..."
"$CURL_CMD" -s -L "$BASE_URL/recipes?search=saumon" | "$JQ_CMD" .
echo "\n--- End Request ---"

# 6. Add a Comment to Pizza (NO JQ)
echo "\n[Step 6] Adding comment to Pizza (ID: $PIZZA_ID)..."
COMMENT_DATA="{\"content\": \"Test comment from sousou via curl! v4\"}"
authenticated_request POST "/recipes/$PIZZA_ID/comments" "$COMMENT_DATA"

# 7. Like the Escargots Recipe (NO JQ)
echo "\n[Step 7] Liking Escargots (ID: $ESCARGOTS_ID)..."
authenticated_request POST "/recipes/$ESCARGOTS_ID/like"
echo "\nLike request sent. Check raw response above and JSON files."

# 8. Unlike the Escargots Recipe (NO JQ)
sleep 1
echo "\n[Step 8] Unliking Escargots (ID: $ESCARGOTS_ID)..."
authenticated_request DELETE "/recipes/$ESCARGOTS_ID/like"
echo "\nUnlike request sent. Check raw response above and JSON files."

# 9. Add a Photo URL to Sushi (authored by sousou) (NO JQ)
echo "\n[Step 9] Adding photo to Sushi (ID: $SUSHI_ID)..."
PHOTO_DATA="{\"url\": \"https://via.placeholder.com/600/sushi.jpg\"}"
authenticated_request POST "/recipes/$SUSHI_ID/photos" "$PHOTO_DATA"


echo "\n[Step 10] Getting user info for $USERNAME (ID: $USER_ID)..."
authenticated_request GET "/users/$USER_ID" "" "true"

# 11. Ask for 'Traducteur' Role (NO JQ)
echo "\n[Step 11] Asking for 'Traducteur' role for $USERNAME..."
ROLE_REQUEST_DATA="{\"requested_role\": \"DemandeTraducteur\"}"
authenticated_request PATCH "/users/$USER_ID/askrole" "$ROLE_REQUEST_DATA"

# 12. Logout (NO JQ)
echo "\n[Step 12] Logging out..."
authenticated_request POST "/auth/logout"
echo "Logout request sent. Check raw response above."

# 13. Clean up cookie file
echo "\n[Step 13] Removing cookie file..."
rm -f "$COOKIE_FILE"
echo "Done."

# --- Restore JSON files ---
echo "\n[Step 14] Restoring JSON files from backup..."

if [ ! -d "$BACKUP_DIR" ]; then
    echo "Error: Backup directory '$BACKUP_DIR' not found. Skipping restore."
else

    if [ -f "$BACKUP_DIR/recipes.json" ]; then
        echo "Copying $BACKUP_DIR/recipes.json to api/data/recipes.json..."
        cp -f "$BACKUP_DIR/recipes.json" "api/data/recipes.json"
        echo "recipes.json restored."
    else
        echo "Warning: Backup file '$BACKUP_DIR/recipes.json' not found. Cannot restore."
    fi

    if [ -f "$BACKUP_DIR/users.json" ]; then
        echo "Copying $BACKUP_DIR/users.json to api/data/users.json..."
        cp -f "$BACKUP_DIR/users.json" "api/data/users.json"
        echo "users.json restored."
    else
        echo "Warning: Backup file '$BACKUP_DIR/users.json' not found. Cannot restore."
    fi
fi
echo "Restore step finished."

echo "\nAPI Test Script Finished."
