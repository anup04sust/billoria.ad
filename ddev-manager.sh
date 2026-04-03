#!/bin/bash

# DDEV Manager Script for Billoria.ad
# Manages DDEV operations for both cmsapi (backend) and frontendapp (frontend)

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Project directories
BACKEND_DIR="./cmsapi"
FRONTEND_DIR="./frontendapp"

# Function to print colored messages
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Function to run ddev command in a directory
run_ddev_command() {
    local dir=$1
    local cmd=$2
    local project_name=$3
    
    if [ -d "$dir" ]; then
        print_info "Running 'ddev $cmd' in $project_name ($dir)..."
        cd "$dir" && ddev "$cmd" && cd - > /dev/null
        print_info "✓ $project_name: ddev $cmd completed"
    else
        print_error "Directory $dir not found. Skipping $project_name."
        return 1
    fi
}

# Function to show usage
show_usage() {
    cat << EOF
Usage: $0 [COMMAND] [OPTIONS]

Commands:
    start       Start both DDEV projects
    restart     Restart both DDEV projects
    poweroff    Power off both DDEV projects
    stop        Stop both DDEV projects (alias for poweroff)
    status      Show status of both DDEV projects
    logs        Show logs for both DDEV projects

Options:
    --backend-only    Run command only for backend (cmsapi)
    --frontend-only   Run command only for frontend (frontendapp)
    -h, --help       Show this help message

Examples:
    $0 start                    # Start both projects
    $0 restart --backend-only   # Restart only backend
    $0 poweroff                 # Power off both projects
    $0 status                   # Check status of both projects

EOF
}

# Main script logic
main() {
    local command=$1
    local option=$2
    
    # Check if ddev is installed
    if ! command -v ddev &> /dev/null; then
        print_error "DDEV is not installed or not in PATH"
        exit 1
    fi
    
    # Handle empty command
    if [ -z "$command" ]; then
        print_error "No command specified"
        show_usage
        exit 1
    fi
    
    # Handle help
    if [ "$command" = "-h" ] || [ "$command" = "--help" ]; then
        show_usage
        exit 0
    fi
    
    # Determine which projects to run command on
    local run_backend=true
    local run_frontend=true
    
    if [ "$option" = "--backend-only" ]; then
        run_frontend=false
    elif [ "$option" = "--frontend-only" ]; then
        run_backend=false
    fi
    
    # Execute command based on input
    case $command in
        start|restart|poweroff|stop)
            print_info "=== Running DDEV $command ==="
            
            if [ "$run_backend" = true ]; then
                run_ddev_command "$BACKEND_DIR" "$command" "Backend (cmsapi)" || true
            fi
            
            if [ "$run_frontend" = true ]; then
                run_ddev_command "$FRONTEND_DIR" "$command" "Frontend (frontendapp)" || true
            fi
            
            print_info "=== DDEV $command completed ==="
            ;;
            
        status)
            print_info "=== DDEV Status ==="
            
            if [ "$run_backend" = true ]; then
                print_info "Backend (cmsapi) status:"
                cd "$BACKEND_DIR" && ddev status && cd - > /dev/null
                echo ""
            fi
            
            if [ "$run_frontend" = true ]; then
                print_info "Frontend (frontendapp) status:"
                cd "$FRONTEND_DIR" && ddev status && cd - > /dev/null
            fi
            ;;
            
        logs)
            if [ "$run_backend" = true ]; then
                print_info "Backend (cmsapi) logs:"
                cd "$BACKEND_DIR" && ddev logs && cd - > /dev/null
                echo ""
            fi
            
            if [ "$run_frontend" = true ]; then
                print_info "Frontend (frontendapp) logs:"
                cd "$FRONTEND_DIR" && ddev logs && cd - > /dev/null
            fi
            ;;
            
        *)
            print_error "Unknown command: $command"
            show_usage
            exit 1
            ;;
    esac
}

# Run main function with all arguments
main "$@"
