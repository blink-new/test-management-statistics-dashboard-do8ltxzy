# Test Management and Statistics Dashboard

A comprehensive PHP web application for creating and managing tests with detailed statistics and analytics.

## Features

### 🎯 Test Management
- Create tests with custom questions from different groups
- Set time limits for tests
- Edit and delete existing tests
- Organize questions by categories/groups

### 📝 Question Bank
- Create questions with 4 multiple-choice answers
- Organize questions into groups (Mathematics, Science, History, Programming, etc.)
- Visual question management with correct answer highlighting

### 📊 Statistics & Analytics
- Comprehensive dashboard with key metrics
- Test performance analysis
- Question difficulty analysis
- Recent test attempts tracking
- Participant performance insights

### 🎓 Test Taking Experience
- Clean, intuitive test interface
- Real-time progress tracking
- Timer functionality with visual countdown
- Automatic submission when time expires
- Detailed results with answer review

### 📈 Results & Reporting
- Detailed score breakdown
- Question-by-question review
- Performance visualization
- Participant information tracking

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: SQLite (portable, no setup required)
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Tailwind CSS
- **Icons**: Lucide Icons
- **Fonts**: Inter (Google Fonts)

## Installation

1. **Clone or download** the project files to your web server directory

2. **Ensure PHP is installed** (version 7.4 or higher recommended)

3. **Set up web server** (Apache, Nginx, or PHP built-in server)

4. **Configure permissions** - ensure the `database/` directory is writable:
   ```bash
   chmod 755 database/
   ```

5. **Access the application** via your web browser

## Quick Start with PHP Built-in Server

```bash
# Navigate to project directory
cd test-management-dashboard

# Start PHP built-in server
php -S localhost:8000

# Open browser and go to:
# http://localhost:8000
```

## Database

The application uses SQLite for simplicity and portability. The database file is automatically created at `database/test_management.db` on first run.

### Sample Data
The application includes sample data:
- 4 question groups (Mathematics, Science, History, Programming)
- 8 sample questions across different categories
- Ready-to-use test environment

## File Structure

```
├── index.php              # Main application entry point
├── config/
│   └── database.php       # Database configuration and setup
├── includes/
│   └── functions.php      # Helper functions
├── pages/
│   ├── dashboard.php      # Dashboard page
│   ├── tests.php          # Tests management
│   ├── create-test.php    # Create new test
│   ├── edit-test.php      # Edit existing test
│   ├── questions.php      # Question management
│   ├── statistics.php     # Statistics and analytics
│   ├── take-test.php      # Test taking interface
│   └── results.php        # Test results display
├── assets/
│   └── js/
│       └── main.js        # JavaScript functionality
├── database/              # SQLite database directory
└── .htaccess             # Apache configuration
```

## Usage

### 1. Dashboard
- View overall statistics
- Quick access to main features
- Recent tests overview

### 2. Managing Questions
- Go to "Questions" section
- Create question groups for organization
- Add questions with 4 multiple-choice answers
- Mark the correct answer

### 3. Creating Tests
- Go to "Tests" section
- Click "Create Test"
- Set test details (title, description, time limit)
- Select questions from your question bank
- Save the test

### 4. Taking Tests
- Click "Take Test" on any test
- Enter participant information
- Answer questions within time limit
- Submit and view results

### 5. Viewing Statistics
- Access detailed analytics in "Statistics"
- View test performance metrics
- Analyze question difficulty
- Track participant progress

## Features in Detail

### Test Creation
- **Flexible Question Selection**: Choose any combination of questions from your question bank
- **Time Management**: Set custom time limits or allow unlimited time
- **Group Organization**: Questions are organized by subject/category
- **Real-time Preview**: See exactly how your test will appear to participants

### Test Taking
- **Progress Tracking**: Visual progress bar shows completion status
- **Timer Integration**: Real-time countdown with color-coded warnings
- **Auto-save**: Prevents data loss during test taking
- **Responsive Design**: Works on desktop, tablet, and mobile devices

### Analytics
- **Performance Metrics**: Track success rates, average scores, completion times
- **Question Analysis**: Identify difficult questions that need review
- **Participant Insights**: Monitor individual and group performance
- **Visual Reports**: Charts and graphs for easy data interpretation

## Security Features

- Input sanitization and validation
- SQL injection prevention using prepared statements
- XSS protection with proper output escaping
- CSRF protection for form submissions
- Secure session management

## Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the MIT License.

## Support

For support, please create an issue in the project repository or contact the development team.

---

**Built with ❤️ using PHP, SQLite, and modern web technologies**