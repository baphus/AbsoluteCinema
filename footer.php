    <style>
/* Footer */
footer {
    background-color: #4193c3;
    color: white;
    padding: 40px 20px;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
}

.footer-column h3 {
    font-weight: 700;
    margin-bottom: 20px;
    font-size: 1.2rem;
}

.footer-column ul {
    list-style: none;
}

.footer-column li {
    margin-bottom: 10px;
}

.footer-column a {
    color: white;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-column a:hover {
    color: #1a2f38;
}
        </style>

</head>
  
<footer>
    <div class="footer-content">
      <div class="footer-column">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="about.html">About us</a></li>
          <li><a href="contact.html">Contact</a></li>
          <li><a href="faq.html">FAQ</a></li>
        </ul>
      </div>
      
      <div class="footer-column contact-info">
        <h3>Contact Us</h3>
        <p>Email: info@absolutecinema.com</p>
        <p>Phone: +12 456 7890</p>
      </div>
      
      <div class="footer-column">
        <h3>Follow Us</h3>
        <div class="social-links">
          <a href="#">Facebook</a>
          <a href="#">Twitter</a>
          <a href="#">Instagram</a>
        </div>
      </div>
    </div>
  </footer>