output "server_public_ip" {
  description = "Public IP of the EC2 instance"
  value       = aws_instance.channelling_server.public_ip
}