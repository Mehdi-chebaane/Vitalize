import os
from flask import Flask, request, jsonify, abort
from flask_sqlalchemy import SQLAlchemy
import pymysql
import json

pymysql.install_as_MySQLdb()

app = Flask(__name__)
app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql://root:@127.0.0.1:3306/vitalize2'
db = SQLAlchemy(app)

class Users(db.Model):
    __tablename__ = 'users'

    id = db.Column(db.Integer, primary_key=True)
    email = db.Column(db.String(180), unique=True, nullable=False)
    roles = db.Column(db.JSON, nullable=False)
    password = db.Column(db.String, nullable=False)
    nom = db.Column(db.String(255), nullable=False)
    prenom = db.Column(db.String(255), nullable=False)
    status = db.Column(db.Boolean, nullable=False)
    tel = db.Column(db.String(255), nullable=False)
    num_cnam = db.Column(db.String(255), nullable=True)
    adresse = db.Column(db.String(255), nullable=True)
    avatar = db.Column(db.String(255))
    reset_token = db.Column(db.String(255))

    def __repr__(self):
        return f"<Users(id={self.id}, email='{self.email}', roles={self.roles}, nom='{self.nom}', prenom='{self.prenom}', status={self.status}, tel='{self.tel}', num_cnam='{self.num_cnam}', adresse='{self.adresse}', avatar='{self.avatar}', reset_token='{self.reset_token}')>"

class Publication(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    type = db.Column(db.String(255), nullable=False)
    titre = db.Column(db.String(255), nullable=False)
    description = db.Column(db.String(5000), nullable=False)
    image = db.Column(db.String(3000), nullable=False)
    video = db.Column(db.String(255))
    id_user = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    commentaires = db.relationship('Commentaire', backref='publication', lazy=True)
    reacts = db.relationship('React', backref='publication', lazy=True)
    views = db.Column(db.Integer)
    publication_views = db.relationship('PublicationView', backref='publication', lazy=True)

class Commentaire(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    id_user = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    id_pub = db.Column(db.Integer, db.ForeignKey('publication.id'), nullable=False)

class React(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    id_user = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    id_pub = db.Column(db.Integer, db.ForeignKey('publication.id'), nullable=False)
    like_count = db.Column(db.Integer)

class PublicationView(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    id_user = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    id_pub = db.Column(db.Integer, db.ForeignKey('publication.id'), nullable=False)

@app.route('/recommendations', methods=['GET', 'POST'])
def generate_recommendations():
    if request.method == 'POST':
        if not request.json or 'user_id' not in request.json:
            abort(400, 'Invalid request data. Please provide user_id.')
        user_id = request.json['user_id']
        user = Users.query.get(user_id)
        if not user:
            abort(404, 'User not found.')
        recommendations = generate_recommendations_for_user(user)
        return jsonify(recommendations)
    else:
        return "This is a GET request to the recommendations endpoint."

def generate_recommendations_for_user(user):
    return []

if __name__ == '__main__':
    app.run(debug=True)