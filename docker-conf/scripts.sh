# Shared by ./docker and the ./docker-* scripts. Source it; don't run it.

# Git Bash on Windows rewrites arguments that look like paths (/var/www/html -> C:/Program Files/Git/var/www/html).
# Nothing these scripts pass to Docker is a host path, so turn that off for every argument.
export MSYS_NO_PATHCONV=1

cd "$(dirname "${BASH_SOURCE[0]}")/.." || exit 1

APP_DIR=/var/www/html

app_container() {
  docker compose ps -q --status running app 2>/dev/null
}

require_running() {
  if [[ -z "$(app_container)" ]]; then
    echo "Error: the app container is not running."
    echo "Start it with: ./docker start"
    exit 1
  fi
}

# Run a command in the app container, from the project root. Allocates a TTY only when there is one,
# so output can still be piped.
app_exec() {
  local tty=-i
  if [[ -t 0 && -t 1 ]]; then
    tty=-it
  fi

  docker exec "$tty" -w "$APP_DIR" "$(app_container)" "$@"
}
